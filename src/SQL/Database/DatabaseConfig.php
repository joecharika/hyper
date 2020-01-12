<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Database;

use Hyper\Application\HyperApp;
use Hyper\Functions\Obj;

class DatabaseConfig
{
    public $host;
    public $database;
    public $username;
    public $password;

    public function __construct(string $host = null, string $database = null, string $username = null, string $password = null)
    {
        $db = Obj::property(
            HyperApp::config(),
            'db',
            (object)[
                'database' => null,
                'host' => null,
                'username' => null,
                'password' => null
            ]
        );

        $this->database = $database ?? @$db->database;
        $this->host = $host ?? @$db->host;
        $this->username = $username ?? @$db->username;
        $this->password = $password ?? @$db->password;

        $db = null;
    }
}
