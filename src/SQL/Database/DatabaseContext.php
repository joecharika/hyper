<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Database;


use Closure;
use Hyper\Exception\{HyperError, HyperException, NullValueException};
use Hyper\Functions\{Obj, Str};
use Hyper\Models\{Pagination, User};
use Hyper\QueryBuilder\Query;
use Hyper\Reflection\Annotation;
use Hyper\SQL\SqlOperator;
use PDO;
use PDOException;
use function is_int;
use function property_exists;
use function strtolower;
use function trim;

/**
 * Class DatabaseContext
 * @package Hyper\Database
 */
class DatabaseContext
{
    use  HyperError;

    #region _Private

    /** @var string */
    public $model;

    /** @var string */
    public $dbTable;

    /** @var string */
    public $context;

    /** @var Pagination */
    public $pagination;

    /** @var Query */
    private $query;

    /** @var array */
    private $list = null;

    /** @var Closure */
    private $foreignClosure;
    #endregion

    #region Init

    /**
     * DatabaseContext constructor.
     * @param string $context
     * @param DatabaseConfig|null $databaseConfig
     */
    public function __construct(string $context, DatabaseConfig $databaseConfig = null)
    {
        try {
            $this->query = new Query(Database::instance($databaseConfig ?? new DatabaseConfig()));
            $this->context = @array_reverse(explode('\\', strtr($context, ['/' => '\\'])))[0] ?? $context;
            $this->model = self::getModel();;
            $this->dbTable = self::getTable();

            $this->foreignClosure = function ($obj) {
                return Obj::toInstance($this->model, $obj);
            };

            self::boot();
        } catch (HyperException $e) {
            self::error($e);
        }
    }

    /**
     * @return string
     */
    private function getModel()
    {
        $context = $this->context;
        if (class_exists($context))
            return $context;

        $namespace = '\\Models\\';

        if (class_exists($namespace . $context))
            return $namespace . $context;

        $namespace = '\\Hyper\\Models\\';

        if (class_exists($namespace . $context))
            return $namespace . $context;

        return $context;
    }

    private function getTable()
    {
        if (Str::contains(str_replace('/', ' \\', $this->context), '\\')) {
            $sep = explode('\\', $this->context);
            $table = @array_reverse($sep)[0];
        } else $table = $this->context;

        return trim(strtolower(Str::pluralize($table)));

    }
    #endregion

    #region ObjectQuery

    /**
     * @return $this
     * @throws HyperException
     */
    private function boot()
    {
        if (!isset(Database::$tables)) {
            foreach ($this->query->tables()->exec('array', PDO::FETCH_NUM)->getResult() as $item) {
                Database::$tables = array_merge(Database::$tables ?? [], $item);
            }
        }

        if (array_search($this->dbTable, Database::$tables ?? []) === false) {
            try {
                if (class_exists($this->model)) {
                    $classVars = get_class_vars($this->model);

                    $properties = array_map(
                        function ($property) use ($classVars) {
                            $type = Annotation::getPropertyAnnotation($this->model, $property, 'SQLType') ?? 'text';
                            $sqlAttrs = Annotation::getPropertyAnnotation(
                                    $this->model,
                                    $property,
                                    'SQLAttributes'
                                ) ?? '';
                            $value = $classVars[$property];
                            $hasDefault = !isset($value) ? '' : (empty($value) ? '' : "DEFAULT '$value'");
                            return "`$property` $type $sqlAttrs $hasDefault";
                        },
                        array_keys($classVars)
                    );

                    #Add timestamps
                    $properties[] = "`createdAt` TIMESTAMP null";
                    $properties[] = "`updatedAt` TIMESTAMP null";
                    $properties[] = "`deletedAt` TIMESTAMP null";
                    $properties[] = "`modifiedBy` text null";

                    #Execute create table query
                    if ($this->query->createTable($this->dbTable, $properties)->exec(null)->getSuccess() === false)
                        throw new HyperException("Failed to create table");
                } else {
                    throw new HyperException("Model($this->model) does not exist");
                }
            } catch (PDOException $e) {
                throw $e;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function foreignKeys(): array
    {
        $foreignKeys = [];
        if (class_exists($this->model)) {
            $classVars = get_class_vars($this->model);
            foreach ($classVars as $classVar => $value) {
                $classVar = strtolower($classVar);
                if (strpos($classVar, "id") && $classVar !== "id")
                    $foreignKeys[] = strtr($classVar, ['id' => '']);
            }
        } else ("Model({$this->model}) does not exist");

        return $foreignKeys;
    }

    #endregion

    #region ForeignObject

    /**
     * Add foreign lists of items that reference this item on this model
     * @param array $foreignList
     * @return DatabaseContext
     */
    public function lists(array $foreignList)
    {
        if (!isset($this->list)) $this->all();

        foreach ($this->list as $key => $entity) {
            $this->list[$key] = $this->foreignList($entity, $foreignList);
        }
        return $this;
    }

    /**
     * Get all saved objects
     * @param array|null $columns
     * @param array|null $foreignParents
     * @param array|null $foreignLists
     * @return DatabaseContext
     */
    public function all(array $columns = null, array $foreignParents = null, array $foreignLists = null): DatabaseContext
    {
        if (!isset($this->list) || empty($this->list)) {
            $this->list = array_map(
                $this->foreignClosure,
                $this->query
                    ->selectFrom($this->dbTable, $columns)
                    ->whereNotDeleted()
                    ->exec()
                    ->getResult()
                    ->getArrayCopy()
            );

            if (isset($foreignParents))
                $this->parents($foreignParents);

            if (isset($foreignLists))
                $this->lists($foreignLists);
        }
        return $this;
    }

    /**
     * Attach parent objects to entities if list is not empty
     * @param $foreignParents
     * @return DatabaseContext
     */
    public function parents($foreignParents): DatabaseContext
    {
        if (isset($this->list))
            foreach ($this->list as $key => $obj)
                $this->list[$key] = $this->foreignParent($obj, $foreignParents);

        return $this;
    }

    /**
     * Attach a foreign parent
     * @param $entity
     * @param $parents
     * @return object
     */
    protected function foreignParent($entity, $parents)
    {
        if (isset($entity)) :
            foreach ($parents as $foreignKey => $foreignModel) :
                if (isset($entity->$foreignKey)) {
                    $column = is_int($foreignKey) ? $this->context . 'Id' : $foreignKey;

                    if (!property_exists($entity, $foreignKey))
                        self::error("Column, $column does'nt exists");

                    $key = @array_reverse(explode('\\', strtr($foreignModel, ['/' => '\\'])))[0] ?? $foreignModel;

                    $entity->$key = (new DatabaseContext($foreignModel))
                        ->first('id', $entity->$foreignKey);
                }
            endforeach;
            return $entity;
        endif;

        return $entity;
    }
    #endregion

    #region ListQuery

    /**
     * First object, can be with condition where condition is true
     * @param string $column
     * @param string $value
     * @param string $operator
     * @param null $parents
     * @param null $lists
     * @return object|null
     */
    public function first($column = '', $value = '1', $operator = SqlOperator::equal, $parents = null, $lists = null)
    {
        $obj = isset($this->list)
            ? @$this->list[0]
            : $this->query
                ->selectFrom($this->dbTable)
                ->where($column, $value, $operator)
                ->subWhere('deletedAt', 'NULL', SqlOperator::is)
                ->exec($this->model)
                ->getResult();


        if (!isset($obj))
            return null;

        if (isset($parents))
            $obj = $this->foreignParent($obj, $parents);

        if (isset($lists))
            $obj = $this->foreignList($obj, $lists);

        return $obj;
    }

    /**
     * Attach a foreign list of specified entities that rely on this entity
     * @param $entity
     * @param $lists
     * @return object
     */
    protected function foreignList($entity, $lists): object
    {
        foreach ($lists as $key => $foreign) :
            $name = Str::pluralize(@array_reverse(explode('\\', strtr($foreign, ['/' => '\\'])))[0] ?? $foreign);
            $column = is_int($key) ? $this->context . 'Id' : $key;
            $entity->$name = (new DatabaseContext($foreign))
                ->where($column, $entity->id)
                ->toList();
        endforeach;

        return $entity;
    }

    /**
     * Get the list from multi-select executions such as select, where, search etc.
     * @return array
     */
    public function toList(): array
    {
        return $this->list;
    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @return DatabaseContext
     */
    public function where($column, $value, $operator = SqlOperator::equal): DatabaseContext
    {
        $this->list = array_map(
            $this->foreignClosure,
            $this->query
                ->selectFrom($this->dbTable)
                ->where($column, $value, $operator)
                ->subWhere('deletedAt', 'NULL', SqlOperator::is)
                ->exec()
                ->getResult()
                ->getArrayCopy()
        );
        return $this;
    }
    #endregion

    #region FilterQuery

    /**
     * Paginate a result
     * @param int $page
     * @param int $perPage
     * @return Pagination
     */
    public function paginate($page = 1, $perPage = 20): Pagination
    {
        if (!isset($this->list)) $this->all();
        return $this->pagination = new Pagination($this->list, $page, $perPage);
    }

    /**
     * Take limited results from the database or available list
     * @param int $limit
     * @param int $offset
     * @return DatabaseContext
     */
    public function take(int $limit, $offset = 0): DatabaseContext
    {
        $this->list = isset($this->list)
            ? array_slice($this->list, 0, $limit)
            : array_map(
                $this->foreignClosure,
                $this->query
                    ->selectFrom($this->dbTable)
                    ->whereNotDeleted()
                    ->limitOffset($limit, $offset)
                    ->exec()
                    ->getResult()
                    ->getArrayCopy()
            );

        return $this;
    }

    /**
     * @param $search
     * @param null|array $foreignParents
     * @param null|array $foreignLists
     * @return DatabaseContext
     */
    public function search(string $search, array $foreignParents = null, array $foreignLists = null): DatabaseContext
    {
        if (empty($search)) {
            $this->all(null, $foreignParents, $foreignLists);
            return $this;
        }

        $this->query
            ->selectFrom($this->dbTable)
            ->whereNotDeleted()
            ->setQuery('and (');

        $properties = get_class_vars($this->model);

        unset($properties['id']);

        foreach ($properties as $classVar => $value) {
            $this->query->subWhere(
                $classVar,
                "%$search%",
                'like',
                array_key_first($properties) === $classVar ? '' : 'or'
            );
        }

        $this->list = array_map(
            $this->foreignClosure,
            $this->query
                ->setQuery(')')
                ->exec()
                ->getResult()
                ->getArrayCopy()
        );

        if (isset($foreignParents))
            $this->parents($foreignParents);

        if (isset($foreignLists))
            $this->lists($foreignLists);

        return $this;
    }
    #endregion

    #region CrudQuery

    /**
     * Get sum from a certain column
     * @param $column
     * @return float
     */
    public function sum($column): float
    {
        return (float)$this->query
            ->selectSum([$column])
            ->from($this->dbTable)
            ->where('deletedAt', 'NULL', 'is')
            ->exec('float')
            ->getResult();
    }

    /**
     * @param object $entity
     * @return bool
     */
    public function add(object $entity)
    {
        $entity->createdAt = date('Y-m-d h:m:s');
        $entity->modifiedBy = User::getId() ?? 'System default';

        if (!isset($entity->id))
            unset($entity->id);

        $entity = (new FileHandler($this))->uploads((array)$entity);

        return $this->query
            ->insertInto($this->dbTable, $entity, array_keys((array)$entity))
            ->exec(null)
            ->getSuccess();
    }

    /**
     * @param $entity
     * @param bool $soft
     * @param bool $all
     * @return bool|int
     */
    public function delete($entity, $soft = true, $all = false)
    {
        return $all
            ? $this->deleteAll($soft)
            : $this->deleteOne($entity, $soft);
    }

    /**
     * @param $soft
     * @return int
     */
    private function deleteAll($soft): int
    {
        if ($soft) {
            return $this->query
                ->update($this->dbTable, [
                    'deletedAt' => date('Y-m-d h:m:s')
                ])
                ->exec(null)
                ->getAffectedRows();
        }

        return $this->query
            ->delete()
            ->from($this->dbTable)
            ->exec(null)
            ->getAffectedRows();
    }

    /**
     * @param $entity
     * @param $soft
     * @return int
     */
    private function deleteOne($entity, $soft): int
    {
        if (is_int($entity) || is_string($entity))
            $entity = $this->first('id', $entity);

        if ($soft) {
            $entity->deletedAt = date('Y-m-d h:m:s');
            return $this->update($entity) ? 1 : 0;
        } else {
            return $this->query
                ->deleteFrom($this->dbTable, "id|$entity->id")
                ->exec(null)
                ->getAffectedRows();
        }
    }

    #endregion

    #region RecycleBin

    /**
     * UPDATE table entity
     * @param object $entity The entity to update with the new values
     * @return bool Whether the update was successful or not
     */
    public function update($entity)
    {
        @$id = $entity->id;
        $entity = (array)Obj::toInstance($this->model, $entity);
        $entity['updatedAt'] = date('Y-m-d h:m:s');
        $entity['modifiedBy'] = User::getId() ?? 'System default';

        if (!isset($id)) self::error(new NullValueException("Entity does not have an ID"));

        $entity = (new FileHandler($this))->uploads($entity);

        unset($entity['id']);

        return $this->query
            ->updateWhere($this->dbTable, $entity, "id|$id")
            ->exec(null)
            ->getSuccess();
    }

    /**
     * Revive deleted item
     * @param object $entity
     * @return bool
     */
    public function recycle(object $entity): bool
    {
        $entity->deletedAt = null;
        return $this->update($entity);
    }
    #endregion

    #region Getters

    /**
     * Get softly deleted items
     * @return array
     */
    public function recycleBin(): array
    {
        //TODO: implement start and end date for getting recycle items
        return $this->query
            ->selectFrom($this->dbTable)
            ->where('deletedAt', 'null', SqlOperator::isNot)
            ->exec()
            ->getResult()
            ->getArrayCopy();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $query = print_r($this->query, true);
        $list = print_r($this->list, true);

        return <<<EOT
            Context: $this->context,
            DatabaseModel: $this->dbTable,
            ApplicationModel: $this->model,
            Query: $query,
            List: $list,
        EOT;
    }

    #endregion

}

/**
 * Function DatabaseContext
 * Dynamic DatabaseContext context access
 * @param string $context
 * @param DatabaseConfig|null $config
 * @return DatabaseContext
 */
function db(string $context, DatabaseConfig $config = null): DatabaseContext
{
    return new DatabaseContext($context, $config);
}
