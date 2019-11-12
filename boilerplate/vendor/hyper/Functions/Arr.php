<?php

namespace Hyper\Functions;


abstract class Arr
{
    /**
     * Get a value from array of key that you're not sure exists
     *
     * @param array $array
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    public static function key($array, $key, $default = null)
    {
        return array_key_exists("$key", $array) ? ($array["$key"] ?? $default) : $default;
    }

    /**
     * Get a value from array of key that you're not sure exists
     *
     * @param array $array
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    public static function safeArrayGet($array, $key, $default = null)
    {
        return array_key_exists("$key", $array) ? ($array["$key"] ?? $default) : $default;
    }

}