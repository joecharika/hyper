<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Functions;


use function is_array;
use function is_object;

/**
 * Trait Cast
 * @package Hyper\Functions
 */
trait Cast
{
    /**
     * Convert an 'object' to a specific instance, only primitives
     * @param string $name
     * @param $entity
     * @return object
     */
    public static function toInstance(string $name, $entity): object
    {
        $obj = new $name();

        foreach ((array)$entity as $property => $value) {
            if (!is_object($value) && !is_array($value)) {
                $k = trim($property);
                $obj->$k = $value;
            }
        }

        return $obj;
    }

    /**
     * Convert an 'object' to a specific instance, including non-primitives
     * @param string $name
     * @param $entity
     * @return object
     */
    public static function toFullInstance(string $name, $entity): object
    {
        $obj = new $name();

        foreach ((array)$entity as $property => $value) {
            $k = trim($property);
            $obj->$k = $value;
        }

        return $obj;
    }
}