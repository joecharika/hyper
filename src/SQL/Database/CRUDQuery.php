<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Database;


use Hyper\Exception\HyperException;
use PDOException;

trait CRUDQuery
{
    #region Create

    /**
     * Save an entity object to database
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
    #endregion

    #region Read
    #endregion

    #region Update

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

    #region Delete


    /**
     * Delete the given object from this context
     *
     * @param $column
     * @param $operator
     * @param $value
     * @param bool $soft Soft delete condition: true executes a soft delete, false otherwise
     * @return int Number of rows affected, supposed to be 1
     */
    public function deleteWhere($column, $operator, $value, $soft = true): int
    {
        if ($soft) {
            return $this->updateWhere($column, $operator, $value, [
                'deletedAt' => date('Y-m-d h:m:s')
            ]);
        } else {
            $q = $this->query("DELETE FROM `:table` WHERE $column$operator'$value'",
                ["table" => $this->tableName], true);
            return $q->rowCount();
        }
    }

    public function updateWhere($column, $operator, $value, $values)
    {
        $valuesParams = [];

        foreach ($values as $col => $val) {
            array_push($valuesParams, "`$col`=:$col");
        }

        $valuesString = implode(", ", $valuesParams);

        return $this->query(
            "UPDATE `$this->tableName` SET $valuesString WHERE `$column`$operator'$value'",
            $values,
            true
        )
            ->rowCount();

    }

    /**
     * Delete everything
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
    #endregion
}