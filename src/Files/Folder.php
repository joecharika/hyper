<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Files;


use function file_exists;

abstract class Folder
{
    public static function controllers(): string
    {
        return self::root() . 'controllers/';
    }

    public static function root(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/';
    }

    public static function views(): string
    {
        return self::root() . 'views/';
    }

    public static function models(): string
    {
        return self::root() . 'models/';
    }

    public static function assets(): string
    {
        return self::root() . 'assets/';
    }

    public static function helpers(): string
    {
        return self::root() . 'helpers/';
    }

    public static function log(): string
    {
        return self::root() . 'log/';
    }

    public static function create(string $path)
    {
        return file_exists($path) ?: mkdir($path, 0777, true);
    }
}