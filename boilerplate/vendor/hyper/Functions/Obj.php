<?php

namespace Hyper\Functions;


use Hyper\Exception\HyperException;
use function class_exists;
use function get_class_vars;
use function property_exists;

/**
 * Class Obj
 * @package Hyper\Functions
 */
abstract class Obj
{
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
        return null;
    }

    /**
     * get_class_vars
     *
     * @param string $class Class to search for variables
     * @return array
     */
    public static function properties($class): array
    {
        if (!class_exists($class)) (new HyperException)->throw("Class $class does not exist");
        return get_class_vars($class);
    }
}
