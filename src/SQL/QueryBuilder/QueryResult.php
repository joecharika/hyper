<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\QueryBuilder;


use ArrayObject;

/**
 * Class QueryResult
 * @package Hyper\QueryBuilder
 */
class QueryResult
{
    private $query = '', $success = false, $result, $affectedRows;

    /**
     * QueryResult constructor.
     * @param null $query
     */
    public function __construct($query = null)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    /**
     * @param mixed $affectedRows
     * @return QueryResult
     */
    public function setAffectedRows(int $affectedRows)
    {
        $this->affectedRows = $affectedRows;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return QueryResult
     */
    public function setSuccess(bool $success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return ArrayObject|Object|mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return QueryResult
     */
    public function setResult($result)
    {
        $this->result = $result === false ? null : $result;
        return $this;
    }
}