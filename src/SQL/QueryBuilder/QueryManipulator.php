<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\QueryBuilder;


trait QueryManipulator
{
    /**
     * @var string
     */
    private $query;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return QueryManipulator|Query
     */
    public function setQuery(string $query)
    {
        $this->query .= empty($query) ? $query : ((empty($this->query) ? '' : ' ') . $query);
        return $this;
    }

    /**
     */
    public function resetQuery(): void
    {
        $this->query = '';
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param $key
     * @param $value
     * @return QueryManipulator|Query
     */
    public function setParams($key, $value)
    {
        $this->params[":$key"] = $value;

        if (!isset($value))
            str_replace(":$key", 'null', $this->query);

        return $this;
    }

}