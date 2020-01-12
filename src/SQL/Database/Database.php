<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Database;


use Hyper\Exception\DatabaseConnectionException;
use Hyper\Exception\HyperException;
use Hyper\QueryBuilder\Query;
use PDO;
use PDOException;

/**
 * Trait Database
 * @package Hyper\Database
 */
class Database
{
    /** @var array */
    public static $tables;
    /** @var array */
    public static $queries = [];
    /** @var PDO */
    private static $db;

    /**
     * @param DatabaseConfig $databaseConfig
     * @return PDO
     * @throws HyperException
     */
    public static function instance(DatabaseConfig $databaseConfig): PDO
    {
        if (isset(self::$db)) return self::$db;

        $dsn = "mysql:host=$databaseConfig->host;dbname=$databaseConfig->database";

        try {
            self::$db = new PDO($dsn, $databaseConfig->username, $databaseConfig->password);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (!isset(self::$db)) throw new DatabaseConnectionException('Failed to connect');

            return self::$db;
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'Unknown database') > 0) {
                self::create($databaseConfig);
                return self::instance($databaseConfig);
            } else throw new HyperException($msg);
        }
    }

    /**
     * @param DatabaseConfig $db
     * @throws HyperException
     */
    private static function create(DatabaseConfig $db)
    {
        if ((new Query(new PDO("mysql:host=$db->host", $db->username, $db->password)))
                ->createDatabase($db->database)
                ->exec(null)
                ->getSuccess() === false)
            throw new HyperException('Failed to create database');
    }
}