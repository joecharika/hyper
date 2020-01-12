<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Functions;


use Hyper\Exception\HyperError;
use Hyper\Exception\HyperException;
use function class_exists;
use function get_class_vars;
use function property_exists;

/**
 * Class Obj
 * @package Hyper\Functions
 * @method static toInstance(string $className, array|object $entity)
 */
abstract class Obj
{
    use Cast, HyperError;

    /**
     * Get a value from class/object of key that you're not sure exists
     *
     * @param $class
     * @param $property
     * @param $default
     * @return mixed
     */
    public static function property($class, $property, $default = null)
    {
        if (isset($class))
            return property_exists($class, $property) ? ($class->$property ?? $default) : $default;
        return $default;
    }

    /**
     * get_class_vars
     *
     * @param string $class Class to search for variables
     * @return array
     */
    public static function properties(string $class): array
    {
        if (!class_exists($class)) self::error(new HyperException("Class $class does not exist"));
        return get_class_vars($class);
    }
}
