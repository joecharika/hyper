<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Functions;


use function implode;

abstract class Arr
{
    use Cast;

    /**
     * Get a value from array of key that you're not sure exists
     * @param array $array
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    public static function key($array, $key, $default = null)
    {
        return array_key_exists("$key", $array) ? ($array["$key"] ?? $default) : $default;
    }

    public static function spread(array $array, $withKeys = false, $separator = ', ', $keySeparator = ' '): string
    {
        if ($withKeys) {
            $temp = [];
            foreach ($array as $key => $value) {
                $temp[] = $key . $keySeparator . $value;
            }
            $array = $temp;
        }

        return implode($separator, $array);
    }
}