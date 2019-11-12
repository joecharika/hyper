<?php
/**
 * hyper v1.0.0-beta.2 (https://hyper.com/php)
 * Copyright (c) 2019. J.Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Database;

use Closure;
use Exception;
use Hyper\Application\{HyperApp, Request};
use Hyper\Exception\{HyperException, NullValueException};
use Hyper\Functions\{Arr, Obj, Str};
use Hyper\Models\Pagination;
use Hyper\Reflection\Annotation;
use PDO;
use PDOException;
use PDOStatement;
use function array_push;
use function array_splice;
use function class_exists;
use function get_class_vars;
use function strpos;
use function strtolower;
use function strtr;

/**
 * Class DatabaseContext
 * @package hyper\Database
 */
class DatabaseContext
{
    #region Properties
    /** @var Pagination */
    public $pagination;
    /** @var array */
    private $list = null;
    /** @var PDO */
    private $db;
    /** @var DatabaseConfig */
    private $config;
    /** @var string */
    private $table, $tableName, $model;

    /**
     * DatabaseContext constructor.
     * @param string $model
     * @param DatabaseConfig $config
     */
    public function __construct(string $model, DatabaseConfig $config = null)
    {
        $this->model = $model;
        $this->table = '\\Models\\' . ucfirst($model);
        $this->tableName = Str::pluralize("$model");
        $this->config = $config ?? HyperApp::$dbConfig;
        $this->failSafe();
    }

    #endregion

    #region Database

    /**
     * Failsafe call that will create the database and table if they do not exist
     */
    private function failSafe()
    {
        try {
            $this->connect();
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (strpos("$msg", "Unknown database") > 0) $this->createDB();
            else (new HyperException)->throw($msg);
        }

        $this->createIfNotExists();
    }

    /**
     * Initialise database connection for the rest of the execution
     *
     * @throws PDOException
     */
    private function connect()
    {
        $db = HyperApp::config()->db;
        $servername = $db->host;
        $username = $db->username;
        $database = $db->database;
        $password = $db->password;
        try {
            $this->db = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (!isset($this->db)) (new HyperException)->throw("Failed to connect");
        } catch (PDOException $e) {
            throw $e;
        }
    }

    #endregion

    #region Query

    /**
     * Create database from config: web.hyper.json
     * @return void
     */
    private function createDB()
    {
        $db = HyperApp::config()->db;
        $servername = $db->host;
        $username = $db->username;
        $database = $db->database;
        $password = $db->password;

        try {
            $conn = new PDO("mysql:host=$servername", $username, $password);

            if (!isset($conn)) (new HyperException())->throw('Failed to connect to server');
            if (!$conn->query("CREATE DATABASE $database")) (new HyperException())->throw("Failed to create database.");

        } catch (PDOException $exception) {
            (new HyperException())->throw($exception->getMessage());
        }
    }

    /**
     * Create if not exists table
     *
     * @return DatabaseContext
     */
    private function createIfNotExists(): DatabaseContext
    {
        try {
            $this->connect();
            $properties = "";

            if (class_exists($this->table)) {
                $classVars = get_class_vars($this->table);
                foreach ($classVars as $classVar => $value) {
                    $type = $this->getDataType($value, $classVar);
                    $sqlAttrs = Annotation::getPropertyAnnotation($this->table, $classVar, "SQLAttributes") ?? "";
                    $hasDefault = !isset($value) ? "" : (empty($value) ? "" : "DEFAULT $value");
                    $properties .= "`$classVar` $type $sqlAttrs $hasDefault,";
                }
                $properties = $this->addTimeStamps($properties);
                if ($this->query("CREATE TABLE IF NOT EXISTS `$this->tableName`($properties)") === false)
                    (new HyperException)->throw("Failed to create table.");
            } else {
                (new HyperException)->throw("Model($this->table) does not exist");
            }
        } catch (PDOException $e) {
            (new HyperException)->throw($e->getMessage());
        }
        return $this;
    }

    /**
     * Get the sql data type
     * @param $value
     * @param $name
     * @return string
     */
    private function getDataType($value, $name)
    {
        if (is_bool($value)) return "BIT";
        elseif (is_int($value)) return "INT";
        elseif (is_string($value)) return "TEXT";
        else return Annotation::getPropertyAnnotation($this->table, $name, "SQLType") ?? "TEXT";
    }

    /**
     * @param string $properties
     * @return string
     */
    private function addTimeStamps(string $properties)
    {
        return $properties . "createdAt TIMESTAMP null, updatedAt TIMESTAMP null, deletedAt TIMESTAMP null";
    }

    /**
     * Run a prepared query on the PDO database object
     *
     * @param string $query The query to run
     * @param array $params Parameters to bind if the statement was a template sql
     * @param bool $eagerResult if you want to get the statement object instead returns true if the statement was executed successfully. See PDOStatement::execute
     * @return bool|PDOStatement
     */
    private function query($query, array $params = [], $eagerResult = false)
    {
        try {
            $statement = $this->db->prepare("$query");
            $result = $statement->execute($params);
            return $eagerResult ? $statement : $result;
        } catch (Exception $e) {
            (new HyperException)->throw($e->getMessage());
        }
        return false;
    }

    /**
     * Take limited results from the database or available list
     *
     * @param int $limit
     * @return DatabaseContext
     */
    public function take(int $limit): DatabaseContext
    {
        if (!isset($this->list)) {
            $this->select($limit);
        } else {
            $this->list = array_splice($this->list, $limit);
        }
        return $this;
    }

    /**
     * SELECT * query with limit and offset params
     *
     * @param int|null $limit
     * @param int|null $offset
     * @return DatabaseContext the current database instance
     */
    public function select($limit = null, $offset = null): DatabaseContext
    {
        if (!isset($this->list) || empty($this->list)) {
            $isLimited = !isset($limit) ? "" : "LIMIT $limit";
            $isOffset = !isset($offset) ? "" : "OFFSET $offset";
            $stmt = $this->query("SELECT * FROM `$this->tableName` WHERE `deletedAt` is NULL $isLimited $isOffset", [],
                true);
            if ($stmt->setFetchMode(PDO::FETCH_ASSOC)) {
                $arr = [];
                foreach ($stmt->fetchAll() as $key => $value) {
                    array_push($arr, $this->attachForeignEntities($value));
                }
                $this->list = $arr;
            }
        }
        return $this;
    }

    /**
     * @param $entity
     * @return object
     */
    private function attachForeignEntities($entity)
    {
        if (!is_array($entity)) $entity = (array)$entity;
        foreach ($this->foreignKeys() as $foreign) {
            $entity[$foreign] = (new DatabaseContext($foreign))->firstById($entity[$foreign . 'Id']);
        }
        return $this->fromArray($entity);
    }

    /**
     * @return array
     */
    public function foreignKeys(): array
    {
        $foreignKeys = [];
        if (class_exists($this->table)) {
            $classVars = get_class_vars($this->table);
            foreach ($classVars as $classVar => $value) {
                $classVar = strtolower($classVar);
                if (strpos($classVar, "id") && $classVar !== "id")
                    array_push($foreignKeys, strtr($classVar, ['id' => '']));
            }
        } else (new HyperException)->throw("Model($this->table) does not exist");

        return $foreignKeys;
    }

    /**
     * Get the first item matching a particular id
     *
     * @param $id
     * @param array $with
     * @return object|null
     */
    public function firstById($id, $with = [])
    {

        $stmt = $this->query("SELECT * FROM `$this->tableName` WHERE `id`=:id AND `deletedAt` is null", ["id" => $id],
            true);
        if ($stmt->setFetchMode(PDO::FETCH_ASSOC)) {
            $obj = Arr::safeArrayGet($stmt->fetchAll(), 0, null);

            if (isset($obj)) {
                foreach ($with as $foreign) {
                    $x = Str::pluralize($foreign);
                    $obj[$x] = (new DatabaseContext($foreign))->where($this->model . "Id", "=", $obj['id'])
                        ->toList();
                }

                return $this->attachForeignEntities($obj);
            }

        }
        return null;
    }

    /**
     * Get the list from multi-select executions such as select, where, search etc.
     * NB: whenever you call this method the database connection is disposed and to reuse it use another instance or reinitialise
     *
     * @return array of objects<$this->table> specific to the Database context
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
    public function where($column, $operator, $value): DatabaseContext
    {
        $stmt = $this->query("SELECT * FROM `$this->tableName` WHERE $column$operator'$value' AND `deletedAt` is NULL",
            [], true);

        if ($stmt->setFetchMode(PDO::FETCH_ASSOC)) {
            $arr = [];

            foreach ($stmt->fetchAll() as $entity) {
                array_push($arr, $this->attachForeignEntities($entity));
            }

            $this->list = $arr;
        }

        return $this;
    }

    public function fromArray($entity)
    {
        $d = $this->table;
        $obj = new $d();
        foreach ((array)$entity as $property => $value) {
            $obj->$property = $value;
        }

        return $obj;
    }

    /**
     * Save an entity object to database
     *
     * @param object $entity
     * @return bool
     */
    public function insert(object $entity): bool
    {
        $entity->createdAt = date('Y-m-d h:m:s');
        $entity = $this->uploads((array)$entity);

        $columns = [];
        $valuesParams = [];

        foreach ($entity as $column => $value) {
            array_push($columns, "`$column`");
            array_push($valuesParams, ":$column");
        }

        $columnsString = implode(",", $columns);
        $valuesString = implode(",", $valuesParams);

        try {
            return $this->query("INSERT INTO `$this->tableName`($columnsString) VALUES ($valuesString)", $entity);
        } catch (PDOException $e) {
            (new HyperException)->throw($e->getMessage());
        }
        return false;
    }

    /**
     * @param array $entity
     * @return array
     */
    public function uploads(array $entity): array
    {
        $entityArray = $entity;
        foreach ($entityArray as $item => $value) {
            $isUpload = Annotation::getPropertyAnnotation($this->table, $item, 'isFile');
            if ($isUpload) {
                $file = $this->handleUpload(Obj::property(Request::files(), $item));
                if (!!isset($file)) {
                    $fileType = $entityArray[$item]->type;
                    $uploadType = Annotation::getPropertyAnnotation($this->table, $item, "UploadAs");
                    if ($uploadType === "Base64") {
                        $var = base64_encode(file_get_contents($file));
                        $entityArray[$item] = "data:$fileType;base64,$var";
                    } else {
                        $entityArray[$item] = "/" . $file;
                    }
                }
            }
        }
        return $entityArray;
    }

    /**
     * @param $file
     * @return string|null
     */
    private function handleUpload($file)
    {
        #If there is no file at all then no upload will take place
        if (!isset($file)) return null;

        #If the file has a name but no temporary name hence the file did not reach the server
        if (!empty($file['name']) && empty($file['tmp_name'])) (new HyperException)->throw('This file could not be uploaded');

        #If the temporary name is empty also the file did not reach the server
        if (empty($file['tmp_name'])) return null;

        #Convert the file to an object
        $file = (object)$file;

        #Get the file type and pluralize it
        $type = Str::pluralize(Arr::safeArrayGet(explode('/', $file->type), 0, ''));
        $targetDir = "assets/uploads/$type";

        #Create folder for specific file type if not exists
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

        $targetDir = "$targetDir/";
        $targetFile = $targetDir . basename($file->name);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        #Complete the upload by moving the file into the specific type directory
        if (move_uploaded_file($file->tmp_name, $targetFile)) {
            $newFileName = $targetDir . uniqid() . uniqid() . "." . $imageFileType;
            rename($targetFile, $newFileName);
            return $newFileName;
        } else (new HyperException())->throw('File upload failed');

        return null;
    }

    /**
     * Delete an object from this context by its id
     *
     * @param $id
     * @param bool $soft
     * @return int
     */
    public function deleteById($id, $soft = true): int
    {
        $entity = $this->firstById($id);
        return $this->delete($entity, $soft);
    }

    /**
     * Delete the given object from this context
     *
     * @param object|array $entity The entity to delete
     * @param bool $soft Soft delete condition: true executes a soft delete, false otherwise
     * @return int Number of rows affected, supposed to be 1
     */
    public function delete($entity, $soft = true): int
    {
        if ($soft) {
            $entity->deletedAt = date('Y-m-d h:m:s');
            return $this->update($entity) ? 1 : 0;
        } else {
            $q = $this->query("DELETE FROM `:table` WHERE `id`=:id",
                ["table" => $this->tableName, "id" => $entity->id]);
            return $q->rowCount();
        }
    }

    #endregion

    #region Utils

    /**
     * UPDATE table entity by id
     *
     * @param object $entity The entity to update with the new values
     * @return bool Whether the update was successful or not
     */
    public function update($entity): bool
    {
        $id = $entity->id;

        $newEntity = new $this->table;

        foreach ((array)$entity as $key => $value) {
            if (array_search($key, $this->foreignKeys()) === false) {
                $newEntity->$key = $value;
            }
        }

        $entity = $newEntity;

        if (!isset($id)) (new NullValueException)->throw("Entity does not have an ID");

        $entity->updatedAt = date('Y-m-d h:m:s');
        $entity = $this->uploads((array)$entity);

        $valuesParams = [];
        $update = [];

        foreach ($entity as $column => $value) {
            if ($column !== 'id') {
                array_push($valuesParams, "`$column`=:$column");
                $update[$column] = $value;
            }
        }

        $valuesString = implode(", ", $valuesParams);
        $id = $this->db->quote($id);

        try {
            return $this->query("UPDATE `$this->tableName` SET $valuesString WHERE `id`=$id", $update);
        } catch (PDOException $e) {
            (new HyperException)->throw($e->getMessage());
        }

        return false;
    }

    /**
     * @param bool $soft
     * @return int
     */
    public function deleteAll($soft = true): int
    {
        if ($soft) {
            $this->select();
            $return = true;
            foreach ($this->list as $entity) {
                $entity->deletedAt = date('Y-m-d h:m:s');
                $return = $return && $this->update($entity);
            }
            return $return;
        }
        $q = $this->query("DELETE FROM `:table`", ["table" => $this->tableName]);
        return $q->rowCount();
    }

    /**
     * @param $search
     * @return DatabaseContext
     */
    public function search($search): DatabaseContext
    {
        if (empty($search)) {
            $this->select();
            return $this;
        }

        $searchArray = [];

        foreach (get_class_vars($this->table) as $classVar => $value) {
            array_push($searchArray, "`$this->tableName`.`$classVar` LIKE :search");
        }

        $searchString = implode(" OR ", $searchArray);

        $stmt = $this->query("SELECT * FROM `$this->tableName` WHERE  `deletedAt` is NULL AND ($searchString)",
            [":search" => "%$search%"], true);

        $arr = [];

        if ($stmt->setFetchMode(PDO::FETCH_ASSOC)) {
            foreach ($stmt->fetchAll() as $entity) {
                array_push($arr, $this->attachForeignEntities($entity));
            }
        }

        $this->list = $arr;

        return $this;
    }

    /**
     * @param Closure $condition
     * @return DatabaseContext
     */
    public function whereClosure(Closure $condition): DatabaseContext
    {
        if (!isset($condition)) (new NullValueException)->throw("Closure cannot be null.");

        try {
            $this->select();
            $arr = [];
            foreach ($this->list as $k => $v) {
                if ($condition((object)$v)) array_push($arr, (object)$v);
            }
            $this->list = $arr;
        } catch (PDOException $e) {
            (new HyperException)->throw($e->getMessage());
        }
        return $this;
    }

    /**
     * @param $column
     * @param null $limit
     * @param null $offset
     * @return float
     */
    public function sum($column, $limit = null, $offset = null): float
    {
        $isLimited = !isset($limit) ? '' : "LIMIT $limit";
        $isOffset = !isset($offset) ? '' : "OFFSET $offset";

        $stmt = $this->query(
            "SELECT SUM(`$this->tableName`.`$column`) FROM `$this->tableName` WHERE `deletedAt` is NULL $isLimited $isOffset",
            [],
            true
        );

        if ($stmt->setFetchMode(PDO::FETCH_ASSOC))
            return (float)$stmt->fetch();

        return 0;
    }

    /**
     * Get the first element
     *
     * @param Closure|null $closure Function that takes an object and is supposed to return a bool.
     * @param array $with
     * @return object|null
     */
    public function first(Closure $closure = null, $with = [])
    {
        if (!isset($this->list)) $this->select();

        foreach ($this->list as $k => $v) {
            $entity = null;

            foreach ($with as $foreign) {
                $x = Str::pluralize($foreign);
                $v[$x] = (new DatabaseContext($foreign))->where($this->table . "Id", "=", $v['id'])->toList();
            }

            if (!isset($closure)) {
                $entity = $v;
            } elseif ($closure((object)$v)) $entity = $v;
            else return null;

            return $this->attachForeignEntities($entity);
        }

        return null;
    }

    /**
     * Add compound lists of specified items that rely on this model
     * @param array $foreignList
     * @return DatabaseContext
     */
    public function with(array $foreignList): DatabaseContext
    {
        if (!isset($foreignList)) (new NullValueException)->throw("models to attach cannot be null.");

        if (!isset($this->list)) $this->select();

        $arr = [];
        foreach ($foreignList as $foreign) {
            foreach ($this->list as $k => $v) {
                $d = (object)$v;
                $x = Str::pluralize($foreign);
                $d->$x = (new DatabaseContext($foreign))->where($this->model . 'Id', '=', $d->id)->toList();
                array_push($arr, $this->attachForeignEntities($v));
            }
        }
        $this->list = $arr;

        return $this;
    }

    /**
     * Paginate a result
     *
     * @param int $page
     * @param int $perPage
     * @return Pagination
     */
    public function paginate($page = 1, $perPage = 20): Pagination
    {
        if (empty($this->list))
            $this->select();

        return new Pagination($this->list, $page, $perPage);

    }

    #endregion
}