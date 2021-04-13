<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\SQL\Database {


    use Closure;
    use DateTime;
    use Exception;
    use Hyper\{Application\Annotations\sqlAttributes,
        Application\Annotations\sqlName,
        Application\Annotations\sqlType,
        Application\Annotations\virtual,
        Exception\HyperError,
        Exception\HyperException,
        Functions\Arr,
        Functions\Debug,
        Functions\Obj,
        Functions\Str,
        Models\Pagination,
        Models\User,
        SQL\QueryBuilder\Query,
        SQL\QueryBuilder\QueryResult,
        SQL\SqlOperator
    };
    use PDO;
    use PDOException;
    use ReflectionException;
    use ReflectionProperty;
    use function array_filter;
    use function array_key_exists;
    use function array_key_first;
    use function array_keys;
    use function array_map;
    use function array_reverse;
    use function array_slice;
    use function class_exists;
    use function date;
    use function explode;
    use function get_class_vars;
    use function is_array;
    use function is_int;
    use function is_null;
    use function is_string;
    use function json_encode;
    use function print_r;
    use function property_exists;
    use function strcmp;
    use function strpos;
    use function strtolower;
    use function strtr;
    use function trim;
    use function usort;

    /**
     * Class DatabaseContext
     * @package Hyper\Database
     */
    class DatabaseContext
    {
        use  HyperError;

        #region _Private
        private static array $_defs = [];

        /** @var string */
        public string $model;

        /** @var string */
        public string $dbTable;

        /** @var string */
        public $context;

        /** @var Pagination */
        public Pagination $pagination;
        protected bool $useCacheQuery = true;
        /** @var Query */
        private Query $query;
        /** @var ?array */
        private ?array $list = null;
        /** @var Closure */
        private Closure $foreignClosure;
        #endregion

        #region Init

        /**
         * DatabaseContext constructor.
         * @param string $context
         * @param DatabaseConfig|null $databaseConfig
         * @param bool $useQueryCache
         */
        public function __construct(string $context, DatabaseConfig $databaseConfig = null, $useQueryCache = true)
        {
            try {
                $this->query = new Query(Database::instance($databaseConfig ?? new DatabaseConfig()), null, '',
                    $this->useCacheQuery = $useQueryCache);
                $this->context = @array_reverse(explode('\\', strtr($context, ['/' => '\\'])))[0] ?? $context;
                $this->model = self::getModel();
                $this->dbTable = self::getTable();
                $this->foreignClosure = fn($obj) => Obj::toInstance($this->model, $obj);

                self::boot();
            } catch (HyperException $e) {
                self::error($e);
            }
        }

        /**
         * @return string
         */
        private function getModel(): string
        {
            $context = $this->context;
            if (class_exists($context)) return $context;

            $namespace = '\\Models\\';

            if (class_exists($namespace . $context)) return $namespace . $context;

            $namespace = '\\Hyper\\Models\\';

            if (class_exists($namespace . $context)) return $namespace . $context;

            return $context;
        }

        /**
         * @return false|string
         */
        private function getTable()
        {
            $name = sqlName::of($this->model);

            if (!empty($name)) return $name;

            if (Str::contains(str_replace('/', ' \\', $this->context), '\\')) {
                $sep = explode('\\', $this->context);
                $table = @array_reverse($sep)[0];
            } else $table = $this->context;

            return trim(Str::toSnake(Str::pluralize($table), '-'));

        }

        /**
         * @return $this
         * @throws HyperException
         */
        private function boot(): DatabaseContext
        {
            if (!isset(Database::$tables)) {
                foreach ($this->query->tables()->exec('array', PDO::FETCH_NUM)->getResult() as $item) {
                    Database::$tables = array_merge(Database::$tables ?? [], $item);
                }
            }

            if (array_search($this->dbTable, Database::$tables ?? []) === false) {
                try {
                    if (class_exists($this->model)) {
                        $classVars = array_filter(get_class_vars($this->model), function ($item) {
                            return !virtual::of($this->model, $item);
                        }, ARRAY_FILTER_USE_KEY);

                        $properties = array_map(
                            function ($property) use ($classVars) {

                                $type = $this->getSqlType($property);
                                $sqlAttrs = sqlAttributes::of($this->model, $property) ?? '';
                                $name = Str::emptyOrNullReplace(sqlName::of($this->model, $property), $property);
                                $value = $classVars[$property];
                                $hasDefault = !isset($value) ? '' : (empty($value) ? '' : "default '$value'");

                                return "`$name` $type $sqlAttrs $hasDefault";
                            },
                            array_keys($classVars)
                        );

                        #Add timestamps
                        $properties[] = "`createdAt` TIMESTAMP null";
                        $properties[] = "`updatedAt` TIMESTAMP null";
                        $properties[] = "`deletedAt` TIMESTAMP null";
                        $properties[] = "`modifiedBy` varchar(100) null";

                        #Execute create table query
                        if ($this->query->createTable(
                                $this->dbTable,
                                $properties,
                                sqlAttributes::of($this->model)
                            )
                                ->exec(null)->getSuccess() === false)
                            throw new HyperException("Failed to create table");
                    } else {
                        throw new HyperException("[ HyperModelException::MissingModel ] $this->model could not be found.");
                    }
                } catch (PDOException $e) {
                    throw $e;
                }
            }

            return $this;
        }

        /**
         * @param $property
         * @return string
         */
        private function getSqlType($property): string
        {
            $type = sqlType::of($this->model, $property);

            if (!empty($type)) return $type;

            try {
                $reflectionType = (new ReflectionProperty($this->model, $property))->getType();
                $type = is_null($reflectionType) ? 'text' : $reflectionType->getName();

                if ($type === 'string') return 'varchar(255)';

                if (!empty($type)) return $type;

            } catch (ReflectionException $e) {
                # Reflection error default to text type
            }

            return 'text';
        }

        #endregion

        #region ObjectQuery

        public static function of($context): DatabaseContext
        {
            return new DatabaseContext($context);
        }

        #endregion

        #region ForeignObject

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
        #endregion

        #region ListQuery

        /**
         * Attach a foreign parent
         * @param $entity
         * @param $parents
         * @return object
         */
        protected function foreignParent($entity, $parents): object
        {
            if (isset($entity)) :
                foreach ($parents as $foreignKey => $foreignModel) :
                    $foreignContext = new DatabaseContext($foreignModel, null, $this->useCacheQuery);
                    $column = is_int($foreignKey) ? strtolower($foreignContext->context) . 'Id' : $foreignKey;

                    if (isset($entity->$column)) {
                        if (!property_exists($entity, $column))
                            self::error("Column, $column does not exist");

                        $key = Str::toPascal($foreignContext->context);
                        $entity->$key = $foreignContext
                            ->first('id', $entity->$column);
                    }
                endforeach;
                return $entity;
            endif;

            return $entity;
        }

        /**
         * First object, can be with condition where condition is true
         * @param string $column
         * @param string $value
         * @param string $operator
         * @param null $parents
         * @param null $lists
         * @return object|null
         */
        public function first($column = '', $value = '1', $operator = SqlOperator::equal, $parents = null, $lists = null): ?object
        {
            $obj = null;

            if (isset($this->list)) {
                if (empty($column) && $value === '1')
                    $obj = @$this->list[0];
                else $obj = @array_filter($this->list ?? [],
                    fn($item) => $item->$column === $value
                )[0];
            }

            $query = $this->whereClause($column, $value, $operator);

            $obj = $obj ?? $query
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
         * @param string|array $column
         * @param string|array $value
         * @param string|array $operator
         * @return Query
         */
        private function whereClause($column, $value, $operator): Query
        {
            $this->startSelect();

            if (is_string($column))
                $this->query->where($column, $value, $operator);

            if (is_array($column)) {
                $this->query->where();

                foreach ($column as $k => $v)
                    $this->query->subWhere("$this->dbTable`.`$v", $value[$k],
                        is_array($operator) ? $operator[$k] : $operator);
            }

            return $this->query;
        }

        /**
         * Attach a foreign list of specified entities that rely on this entity
         * @param $entity
         * @param $lists
         * @param ?string $property
         * @return object
         */
        protected function foreignList($entity, $lists, ?string $property = null): object
        {
            foreach ($lists as $key => $foreign) {
                $name = $property ?? Str::toPascal(Str::pluralize(@array_reverse(explode('\\',
                            strtr($foreign, ['/' => '\\'])))[0] ?? $foreign));
                $column = is_int($key) ? strtolower($this->context) . 'Id' : $key;

                $entity->$name = (new DatabaseContext($foreign, null, $this->useCacheQuery))
                    ->where($column, $entity->id)
                    ->toList();
            }

            return $entity;
        }

        protected function startSelect()
        {
            if (!Str::contains($this->query->getQuery(), 'select'))
                $this->query->selectFrom($this->dbTable)->whereNotDeleted();

            return $this->query;
        }

        /**
         * Get the list from multi-select executions such as select, where, search etc.
         * @return array
         */
        public function toList(): array
        {
            return array_map($this->foreignClosure, $this->list ?? $this->query->exec()->getResult()->getArrayCopy());
        }

        /**
         * @param $column
         * @param $operator
         * @param $value
         * @return DatabaseContext
         */
        public function where($column, $value, $operator = SqlOperator::equal): DatabaseContext
        {
            if (isset($this->list) && !is_array($column)) {
                $this->list = array_filter($this->list,
                    function ($i) use ($operator, $column, $value) {
                        switch ($operator) {
                            case SqlOperator::equal:
                                return $i->$column === $value;
                            case SqlOperator::greaterThan:
                                return $i->$column > $value;
                            case SqlOperator::greaterThanOrEqual:
                                return $i->$column >= $value;
                            default:
                                return true;
                        }
                    }
                );

                return $this;
            } else $this->whereClause($column, $value, $operator);
            return $this;
        }
        #endregion

        #region FilterQuery

        /**
         * Add foreign lists of items that reference this item on this model
         * @param array $foreignList
         * @param string|null $as
         * @return DatabaseContext
         */
        public function lists(array $foreignList, ?string $as = null): DatabaseContext
        {
            if (!isset($this->list)) $this->all();

            foreach ($this->list as $key => $entity) {
                $this->list[$key] = $this->foreignList($entity, $foreignList, $as);
            }

            return $this;
        }

        public function reversed(): DatabaseContext
        {
            if (!isset($this->list)) $this->all();

            $this->list = array_reverse($this->list);

            return $this;
        }

        public function orderBy(array $columns, $asc = true): DatabaseContext
        {
            if (isset($this->list))
                foreach ($columns as $p)
                    usort($this->list, fn($a, $b) => strcmp($a->$p, $b->$p));

            else $this->startSelect()->orderBy($columns, $asc);

            return $this;
        }

        /**
         * Take limited results from the database or available list
         * @param int $limit
         * @param int $offset
         * @return DatabaseContext
         */
        public function take(int $limit, $offset = 0): DatabaseContext
        {
            if (isset($this->list)) array_slice($this->list, 0, $limit);
            else $this->startSelect()->limitOffset($limit, $offset);

            return $this;
        }
        #endregion

        #region CrudQuery

        /**
         * @param $search
         * @param null|array $foreignParents
         * @param null|array $foreignLists
         * @return DatabaseContext
         */
        public function search(?string $search, ?array $foreignParents = null, ?array $foreignLists = null): DatabaseContext
        {
            if (!isset($search) || is_null($search) || empty($search)) {
                if (!isset($this->list))
                    $this->all(null, $foreignParents, $foreignLists);

                return $this;
            }

            if (isset($this->list)) {
                $this->list = array_filter($this->list, function ($item) use ($search) {
                    return Str::contains(strtolower(json_encode($item)), strtolower($search));
                });

                return $this;
            }

            $this->query
                ->selectFrom($this->dbTable)
                ->whereNotDeleted()
                ->setQuery('and (');

            $properties = $this->sanitiser(get_class_vars($this->model), ['id']);

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
                ->where('deletedAt', 'null', 'is')
                ->exec('float')
                ->getResult();
        }

        /**
         * @param object $entity
         * @return object Inserted object
         * @throws Exception
         */
        public function add(object $entity)
        {
            $entity = $this->sanitiser($entity);

            $entity->createdAt = date('Y-m-d h:m:s');
            $entity->modifiedBy = User::getId() ?? 'System default';

            if (!isset($entity->id)) unset($entity->id);

            $entity = (new FileHandler($this))->uploads((array)$entity);

            $result = $this->query
                ->insertInto($this->dbTable, $entity, array_keys((array)$entity))
                ->exec(null);

            if (!$result->getSuccess()) throw new Exception("Failed to add entity to database");

            $entity['id'] = $result->getLastInsertedId();

            return Obj::toInstance($this->model, $entity);
        }

        /**
         * Clean object/array of db incompatible values
         * @param $item
         * @param array $remove
         * @return object|array
         */
        public function sanitiser($item, $remove = [])
        {
            $p = is_array($item) ? $item : (array)$item;

            foreach ($p as $i => $v)
                if (virtual::of($this->model, $i) || Str::contains($i, '\\') || array_key_exists($i, $remove))
                    unset($p[$i]);

            return is_array($item) ? $p : (object)$p;
        }

        /**
         * @param $entity
         * @param bool $soft
         * @param bool $all
         * @return bool|int
         * @throws Exception
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

        #endregion

        #region RecycleBin

        /**
         * @param $entity
         * @param $soft
         * @return int
         * @throws Exception
         */
        private function deleteOne($entity, $soft): int
        {
            if (is_int($entity) || is_string($entity))
                $entity = $this->first('id', $entity);

            if ($soft) {
                return $this->query
                    ->updateWhere($this->dbTable, ['deletedAt' => date('Y-m-d h:m:s')], "id|$entity->id")
                    ->exec(null)->getSuccess() ? 1 : 0;
            } else {
                return $this->query
                    ->deleteFrom($this->dbTable, "id|$entity->id")
                    ->exec(null)
                    ->getAffectedRows();
            }
        }

        /**
         * Update or insert table entity
         * @param object $entity The entity to update with the new values
         * @param string $primaryKey
         * @param bool $returnItem
         * @return object|bool
         * @throws Exception
         */
        public function update(object $entity, $primaryKey = 'id', $returnItem = false)
        {
            $id = null;

            $entity = $this->sanitiser((array)$entity);

            $createdAt = @$entity['createdAt'];

            $entity['createdAt'] = $createdAt instanceof DateTime ? $createdAt->format('Y-m-d h:m:s') : "{$createdAt}";
            $entity['updatedAt'] = date('Y-m-d h:m:s');
            $entity['modifiedBy'] = User::getId() ?? 'System default';

            if (is_null(Arr::key($entity, $primaryKey))) {
                $obj = $this->add((object)$entity);
                return $returnItem ? $obj : isset($obj->$primaryKey);
            } else $id = $entity[$primaryKey];

            $fileHandler = new FileHandler($this);

            $entity = $fileHandler->uploads($entity);

            $fileHandler->cleanUpObjectUploads($old = (new DatabaseContext($this->context, null, false))->first($primaryKey, $id), $entity);

            unset($entity[$primaryKey]);

            $update = [];

            foreach (array_keys($entity) as $k) {
                if ($old->$k !== $entity[$k])
                    $update[$k] = $entity[$k];
            }

            if (empty($update)) $update = $entity;

            $q = $this->query
                ->updateWhere($this->dbTable, $update, "$primaryKey|$id")
                ->exec(null);

            $entity[$primaryKey] = $id;

            return $returnItem ? (object)$entity : $q->getSuccess();
        }
        #endregion

        #region Getters

        /**
         * Revive deleted item
         * @param object $entity
         * @return bool
         * @throws Exception
         */
        public function recycle(object $entity): bool
        {
            $entity->deletedAt = null;
            return $this->update($entity);
        }

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

        #endregion


        #region static

        /**
         * @param string|Query $query
         * @param array|null $params
         * @return QueryResult
         */
        public function query($query, ?array $params = null): QueryResult
        {
            $q = $query instanceof Query
                ? $query
                : new Query(null, $this->dbTable, $query);

            foreach ($params as $key => $value) $q->setParams($key, $value);

            return $q->exec();
        }
        #endregion

        /**
         * @return string
         */
        public function __toString(): string
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

        /**
         * Manually set list
         * @param array $list
         * @return DatabaseContext
         */
        public function setList(array $list): DatabaseContext
        {
            $this->list = $list;
            return $this;
        }

    }

    /**
     * Function DatabaseContext
     * Dynamic DatabaseContext context access
     * @param string $context
     * @param DatabaseConfig|null $config
     * @return DatabaseContext
     * @deprecated Use DatabaseContext::of
     */
    function db(string $context, DatabaseConfig $config = null): DatabaseContext
    {
        return new DatabaseContext($context, $config);
    }
}
