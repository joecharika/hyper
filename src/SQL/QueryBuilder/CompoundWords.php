<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\QueryBuilder;


use Hyper\Functions\Arr;
use Hyper\SQL\SqlOperator;
use function str_replace;

trait CompoundWords
{
    use KeyWords, QueryManipulator;

    /**
     * Creates a new SQL database
     * @param $name
     * @return Query
     */
    function createDatabase($name): Query
    {
        return $this->create()->database($name);
    }

    /**
     * Creates an index on a table (allows duplicate values)
     * @param $name
     * @return Query
     */
    function createIndex($name): Query
    {
        return $this->create("index $name");
    }

    /**
     * Creates a new table in the database
     * @param $tableName
     * @param array $columns
     * @return Query
     */
    function createTable($tableName, array $columns): Query
    {
        $tableName = str_replace('`', '\'', $tableName);
        $this->create('table if not exists');
        return $this->setQuery("`$tableName`(" . Arr::spread($columns) . ")");
    }

    /**
     * Creates a stored procedure
     * @param $name
     * @param $procedure
     * @return Query
     */
    function createProcedure($name, $procedure): Query
    {
        return $this->create()->procedure($name, $procedure);
    }

    /**
     * Creates a unique index on a table (no duplicate values)
     * @param $name
     * @param $columns
     * @return Query
     */
    function createUniqueIndex($name, $columns): Query
    {
        return $this->create(SqlOperator::unique)->index($name);
    }

    /**
     * Creates a view based on the result set of a SELECT statement
     * @param $name
     * @param $select
     * @return Query
     */
    function createView($name, $select): Query
    {
        return $this->create("view $name as $select");
    }


    /**
     * Deletes a column in a table
     */
    public function dropColumn()
    {
    }


    /**
     * Deletes a UNIQUE, PRIMARY KEY, FOREIGN KEY, or CHECK constraint
     */
    public function dropConstraint()
    {
    }


    /**
     * Deletes an existing SQL database
     */
    public function dropDatabase()
    {
    }


    /**
     * Deletes a DEFAULT constraint
     */
    public function dropDefault()
    {
    }


    /**
     * Deletes an index in a table
     */
    public function dropIndex()
    {
    }


    /**
     * Deletes an existing table in the database
     */
    public function dropTable()
    {
    }


    /**
     * Deletes a view
     */
    public function dropView()
    {
    }


    /**
     * Executes a stored procedure
     */
    public function executeProcedure()
    {
    }

}