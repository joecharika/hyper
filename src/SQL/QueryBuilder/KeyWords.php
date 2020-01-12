<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\QueryBuilder;


/**
 * Trait KeyWords
 * @package Hyper\Query
 */
trait KeyWords
{
    use QueryManipulator;

    #region A

    /**
     * Adds a column in an existing table
     * @param $name
     * @return Query
     */
    function add($name = ''): Query
    {
        return $this->setQuery("add $name");
    }

    /**
     * Adds, deletes, or modifies columns in a table, or changes the data type of a column in a table
     * @param $name
     * @return Query
     */
    function alter($name): Query
    {
        return $this->setQuery("alter $name");
    }

    /**
     * Renames a column or table with an alias
     * @param $alias
     * @return Query
     */
    function as($alias): Query
    {
        return $this->setQuery("as $alias");
    }

    /**
     * Sorts the result set in ascending order
     */
    function asc()
    {
        return $this->setQuery('asc');
    }

    #endregion

    #region C

    /**
     * Changes the data type of a column or deletes a column in a table
     * @param $name
     * @return Query
     */
    function column($name = ''): Query
    {
        return $this->setQuery("column $name");
    }

    /**
     * Adds or deletes a constraint
     * @param $constraint
     * @return Query
     */
    function constraint($constraint = ''): Query
    {
        return $this->setQuery("constraint $constraint");
    }

    /**
     * Creates a database, index, view, table, or procedure
     * @param string $name
     * @return Query
     */
    function create(string $name = ''): Query
    {
        return $this->setQuery("create $name");
    }
    #endregion

    #region D
    /**
     * Selects only distinct (different) values
     */
    public function distinct(): Query
    {
        return $this->setQuery('distinct');
    }


    /**
     * Deletes a column, constraint, database, index, table, or view
     */
    public function drop()
    {
    }
    #endregion
}