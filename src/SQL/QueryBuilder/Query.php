<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\QueryBuilder;


use ArrayObject;
use Hyper\Database\{Database, DatabaseConfig};
use Hyper\Application\HyperApp;
use Hyper\Application\HyperEventHook;
use Hyper\Exception\{HyperError, HyperException};
use Hyper\Functions\{Arr, Logger, Str};
use Hyper\SQL\SqlOperator;
use PDO;
use PDOException;
use function class_exists;
use function explode;

/**
 * Class Query
 * @package Hyper\SQL
 */
class Query
{
    use KeyWords, CompoundWords, QueryManipulator, HyperError;

    /** @var string */
    private $table;

    /** @var array */
    private $queries = [];

    /** @var PDO */
    private $pdo;

    /**
     * Query constructor.
     * @param PDO|null $pdo
     * @param null $table
     * @param string $startQuery
     */
    public function __construct(PDO $pdo = null, $table = null, $startQuery = '')
    {
        try {
            $this->pdo = $pdo ?: Database::instance(new DatabaseConfig());
            $this->table = $table;
            $this->setQuery($startQuery);
        } catch (HyperException $e) {
            self::error($e);
        }
    }

    /**
     * Adds a constraint after a table is already created
     * @param $constraint
     * @return Query
     */
    public function addConstraint($constraint): Query
    {
        return $this->add()->constraint($constraint);
    }


    /**
     * Changes the data type of a column in a table
     * @param $column
     * @return Query
     */
    public function alterColumn($column): Query
    {
        return $this->alter('column')->column($column);
    }

    /**
     * Adds, deletes, or modifies columns in a table
     * @param $tableName
     * @return Query
     */
    public function alterTable($tableName)
    {
        return $this->alter("table $tableName");
    }

    /**
     * Creates different outputs based on conditions
     * @param $column
     * @param array $cases
     * @param $default
     * @return Query
     */
    public function case($column, array $cases, $default): Query
    {
        $this->setQuery("(case `$column`");

        foreach ($cases as $key => $case) {
            $this->setQuery("when $case then $key");
        }

        return $this->setQuery("else $default end)");
    }


    /**
     * A constraint that limits the value that can be placed in a column
     * @param $column
     * @param $operator
     * @param $value
     * @return Query
     */
    public function check($column, $operator, $value): Query
    {
        return $this->setQuery("check ($column $operator $value)");
    }


    /**
     * Creates or deletes an SQL database
     * @param $name
     * @return Query
     */
    public function database($name = ''): Query
    {
        $name = empty($name) ? $name : " `$name`";
        return $this->setQuery("database{$name}");
    }


    /**
     * A constraint that provides a default value for a column
     * @param string $value
     * @return Query
     */
    public function default($value = '')
    {
        return $this->setQuery("default $value");
    }

    /**
     * Deletes rows from a table
     * @param $tableName
     * @param $identifier
     * @return Query|QueryManipulator
     */
    public function deleteFrom($tableName, $identifier)
    {
        $explode = explode('|', $identifier);

        return $this
            ->delete()
            ->from($tableName)
            ->where(
                Arr::key($explode, 0, ''),
                Arr::key($explode, 1, '1')
            );
    }

    /**
     * Filters a result set to include only records that fulfill a specified condition
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return Query|QueryManipulator
     */
    public function where($column = '', $value = '1', $operator = '='): Query
    {
        if (Str::contains($this->query, 'where'))
            return $this->subWhere($column, $value, $operator);

        $this->sanitize($column, $operator, $value);
        return $this->setQuery("where $column$operator$value");
    }

    /**
     * Filters a result set to include only records that fulfill a specified condition
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param string $punctuation
     * @return Query|QueryManipulator
     */
    public function subWhere($column = '', $value = '1', $operator = '', $punctuation = 'and'): Query
    {
        $this->sanitize($column, $operator, $value);
        return $this
            ->setQuery("$punctuation $column$operator$value");
    }

    /**
     * Clean and quote column & value
     * @param $column
     * @param $operator
     * @param $value
     */
    private function sanitize(&$column, &$operator, &$value): void
    {
        $value = Str::contains($value, 'null') ? $value : '"' . str_replace('\'', '\\\'', $value) . '"';
        $column = empty($column) ? $column : "`$column`";
        $operator = empty($operator) ? $operator
            : (empty($column) ? '' : " $operator ");
    }

    /**
     * Specifies which table to select or delete data from
     * @param $tableName
     * @return Query
     */
    public function from($tableName): Query
    {
        return $this->setQuery("from `$tableName`");
    }

    /**
     * Deletes rows from a table
     * @return Query|QueryManipulator
     */
    public function delete()
    {
        return $this->setQuery('delete');
    }

    public function whereNotDeleted()
    {
        return $this->where('deletedAt', 'NULL', SqlOperator::is);
    }

    public function whereDeleted()
    {
        return $this->where('deletedAt', 'NULL', SqlOperator::isNot);
    }

    /**
     * Sorts the result set in descending order
     */
    public function desc()
    {
    }

    /**
     * Tests for the existence of any record in a subquery
     */
    public function exists()
    {

    }

    /**
     * A constraint that is a key used to link two tables together
     */
    public function foreignKey()
    {
    }

    /**
     * Returns all rows when there is a match in either left table or right table
     */
    public function fullOuterJoin()
    {
    }

    /**
     * Groups the result set (used with aggregate public functions: COUNT, MAX, MIN, SUM, AVG)
     */
    public function groupBy()
    {
    }

    /**
     * Used instead of WHERE with aggregate public functions
     */
    public function having()
    {
    }

    /**
     * Allows you to specify multiple values in a WHERE clause
     */
    public function in()
    {
    }

    /**
     * Creates or deletes an index in a table
     * @param $name
     * @return Query|QueryManipulator
     */
    public function index($name)
    {
        return $this->setQuery("index $name");
    }

    /**
     * Returns rows that have matching values in both tables
     */
    public function innerJoin()
    {
    }

    /**
     * Inserts new rows in a table
     * @param string $tableName
     * @param array $values
     * @param array|null $columns
     * @return Query|QueryManipulator
     */
    public function insertInto(string $tableName, array $values, array $columns = null)
    {
        $columns = isset($columns) ? '(`' . Arr::spread($columns, false, '`,`') . '`)' : '';

        return $this
            ->setQuery('insert')
            ->setQuery("into `$tableName`$columns")
            ->values($values);
    }

    /**
     * Specifies the values of an INSERT INTO statement
     * @param array $values Associative array with the values to insert
     * @return Query|QueryManipulator
     */
    public function values($values)
    {
        foreach ($values as $key => $value) {
            $this->setParams($key, $value);
        };

        return $this->setQuery('values(:' . Arr::spread(array_keys($values), false, ', :') . ')');
    }

    /**
     * Copies data from one table into another table
     */
    public function insertIntoSelect()
    {
    }

    /**
     * Tests for empty values
     */
    public function isNull()
    {
    }

    /**
     * Tests for non-empty values
     */
    public function isNotNull()
    {
    }

    /**
     * Joins tables
     */
    public function join()
    {
    }

    /**
     * Returns all rows from the left table, and the matching rows from the right table
     */
    public function leftJoin()
    {
    }

    /**
     * Searches for a specified pattern in a column
     */
    public function like()
    {
    }

    public function limitOffset(int $limit, int $offset)
    {
        return $this->limit($limit)->offset($offset);
    }

    /**
     * @param int $offset
     * @return Query|QueryManipulator
     */
    public function offset(int $offset)
    {
        return $this->setQuery("offset $offset");
    }

    /**
     * Specifies the number of records to return in the result set
     * @param int $limit
     * @return Query|QueryManipulator
     */
    public function limit(int $limit)
    {
        return $this->setQuery("limit $limit");
    }

    /**
     * Only includes rows where a condition is not true
     */
    public function not()
    {
    }

    /**
     * A constraint that enforces a column to not accept NULL values
     */
    public function notNull(): Query
    {
        return $this->setQuery('not null');
    }

    /**
     * Includes rows where either condition is true
     */
    public function or()
    {
    }

    /**
     * Sorts the result set in ascending or descending order
     */
    public function orderBy()
    {
    }

    /**
     * Returns all rows when there is a match in either left table or right table
     */
    public function outerJoin()
    {
    }

    /**
     * A constraint that uniquely identifies each record in a database table
     */
    public function primaryKey(): Query
    {
        return $this->setQuery('primary key');
    }

    /**
     * A constraint that uniquely identifies each record in a database table
     */
    public function autoIncrement(): Query
    {
        return $this->setQuery('auto_increment');
    }

    /**
     * A stored procedure
     * @param $name
     * @param $procedure
     * @return Query
     */
    public function procedure($name, $procedure): Query
    {
        return $this->setQuery("$name $procedure");
    }

    /**
     * Returns all rows from the right table, and the matching rows from the left table
     */
    public function rightJoin()
    {
    }

    /**
     * Specifies the number of records to return in the result set
     */
    public function rowNum()
    {
    }

    /**
     * Selects data from a database table
     * @param $table
     * @param string|array $columns
     * @return Query
     */
    public function selectFrom($table, $columns = '*'): Query
    {
        return $this
            ->select($columns)
            ->from($table);
    }

    /**
     * Selects columns
     * @param string|array $columns
     * @return Query|QueryManipulator
     */
    public function select($columns = '*'): Query
    {
        $columns = $columns ?? '*';
        $c = is_string($columns) ? $columns : '`' . Arr::spread($columns, false, '`,`') . '`';
        return $this->setQuery("select $c");
    }

    /**
     * Selects data from a database table
     * @param $column
     * @return Query|QueryManipulator
     */
    public function selectSum($column): Query
    {
        return $this
            ->setQuery("select sum($column)");
    }

    /**
     * Selects only distinct (different) values
     */
    public function selectDistinct()
    {
    }

    /**
     * Copies data from one table into a new table
     */
    public function selectInto()
    {
    }

    /**
     * Specifies the number of records to return in the result set
     */
    public function selectTop()
    {
    }

    /**
     * Creates a table, or adds, deletes, or modifies columns in a table, or deletes a table or data inside a table
     */
    public function table()
    {
    }

    /**
     * Creates a table, or adds, deletes, or modifies columns in a table, or deletes a table or data inside a table
     */
    public function tables()
    {
        return $this->show('tables');
    }

    /**
     * @param $object
     * @return Query|QueryManipulator
     */
    public function show($object): Query
    {
        return $this->setQuery("show $object");
    }

    /**
     * Specifies the number of records to return in the result set
     */
    public function top()
    {
    }

    /**
     * Deletes the data inside a table, but not the table itself
     */
    public function truncateTable()
    {
    }

    /**
     * Combines the result set of two or more SELECT statements (only distinct values)
     */
    public function union()
    {
    }

    /**
     * Combines the result set of two or more SELECT statements (allows duplicate values)
     */
    public function unionAll()
    {
    }

    /**
     * A constraint that ensures that all values in a column are unique
     */
    public function unique()
    {
    }

    /**
     * Updates existing rows in a table
     * @param $tableName
     * @param $newValues
     * @param string $identifier Must be in the form $column|$value
     * @return Query
     */
    public function updateWhere($tableName, $newValues, $identifier): Query
    {
        $explode = explode('|', $identifier);

        $column = Arr::key($explode, 0, '');
        $value = Arr::key($explode, 1, '1');

        return $this
            ->update($tableName, $newValues)
            ->where($column, $value);
    }

    /**
     * Updates existing rows in a table
     * @param $tableName
     * @param $newValues
     * @return Query
     */
    public function update($tableName, $newValues): Query
    {
        $this->setQuery("update `$tableName`")->set();

        $values = [];

        foreach ($newValues as $key => $value) {
            $this->setParams($key, $value);
            $values[] = "`$key` = :$key";
        }

        return $this->setQuery(Arr::spread($values));
    }

    /**
     * Specifies which columns and values that should be updated in a table
     * @param $specifier
     * @return Query|QueryManipulator
     */
    public function set($specifier = ''): Query
    {
        return $this->setQuery("set $specifier");
    }

    /**
     * Creates, updates, or deletes a view
     */
    public function view()
    {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getQuery();
    }

    /**
     * Run a prepared query on the PDO database object
     * @param string $fetch
     * @param int $fetchMode PDO fetch mode
     * @return QueryResult
     */
    public function exec($fetch = 'array', $fetchMode = 2): QueryResult
    {
        try {

            HyperApp::event(HyperEventHook::queryExecuting, $this->query);

            $queryResult = !empty(Database::$queries[$this->query]) ?
                (array_key_exists($this->query, Database::$queries)
                    ? $this->executedQuery($this->query)
                    : $this->executeQuery($this->query, $fetch, $fetchMode))
                : $this->executeQuery($this->query, $fetch, $fetchMode);

            $this->resetQuery();


            return $queryResult;

        } catch (PDOException $e) {
            self::error($e->getMessage());
        }

        return new QueryResult();
    }

    private function executedQuery($query): QueryResult
    {
        Logger::log('[ ! ] duplicate: ' . $query, 'QUERY', 'LAST_REQUEST_QUERY');

        $result = Database::$queries[$query];

        return (new QueryResult($query))
            ->setSuccess(true)
            ->setAffectedRows($result instanceof ArrayObject ? $result->count() : 1)
            ->setResult($result);
    }

    /**
     * @param $query
     * @param $fetch
     * @param $fetchMode
     * @return QueryResult
     */
    private function executeQuery($query, $fetch, $fetchMode): QueryResult
    {
        $statement = $this->pdo->prepare($query);
        $success = $statement->execute($this->getParams());

        Logger::log(strtr($query, $this->getParams()), 'QUERY', 'LAST_REQUEST_QUERY');

        $q = (new QueryResult($query))
            ->setSuccess($success)
            ->setAffectedRows($statement->rowCount());

        return isset($fetch) ?
            $q->setResult(Database::$queries[$query] = $fetch === 'array'
                ? new ArrayObject($statement->fetchAll($fetchMode))
                : (class_exists($fetch)
                    ? $statement->fetchObject($fetch)
                    : $statement->fetch()
                ))
            : $q;
    }
}