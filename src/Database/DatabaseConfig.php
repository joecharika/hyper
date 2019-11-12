<?php


namespace Hyper\Database;


use Hyper\Application\HyperApp;

class DatabaseConfig
{
    public $host,
        $database,
        $username,
        $password = '';

    public function __construct(string $host = null, string $database = null, string $username = null, string $password = null)
    {
        $db = HyperApp::config()->db;

        $this->database = $database ?? $db->database;
        $this->host = $host ?? $db->host;
        $this->username = $username ?? $db->username;
        $this->password = $password ?? $db->password;

        $db = null;
    }

}