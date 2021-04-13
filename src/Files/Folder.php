<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Files {


    use function file_exists;
    use function mkdir;

    abstract class Folder
    {
        public static function controllers(): string
        {
            return self::root() . 'controllers' . DIRECTORY_SEPARATOR;
        }

        public static function root(): string
        {
            return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;
        }

        public static function views(): string
        {
            return self::root() . 'templates' . DIRECTORY_SEPARATOR;
        }

        public static function models(): string
        {
            return self::root() . 'models' . DIRECTORY_SEPARATOR;
        }

        public static function assets(): string
        {
            return self::root() . 'assets' . DIRECTORY_SEPARATOR;
        }

        public static function helpers(): string
        {
            return self::root() . 'helpers' . DIRECTORY_SEPARATOR;
        }

        public static function log(): string
        {
            return self::root() . 'log' . DIRECTORY_SEPARATOR;
        }

        public static function create(string $path): bool
        {
            return file_exists($path) ?: mkdir($path, 0777, true);
        }
    }
}