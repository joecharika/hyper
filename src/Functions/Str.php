<?php
/**
 * hyper v1.0.0-beta.2 (https://hyper.com/php)
 * Copyright (c) 2019. J.Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Functions;


use function is_null;
use function lcfirst;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;
use function strtr;
use function substr;
use function ucfirst;
use function ucwords;

/**
 * Class Str alias String
 * @package Hyper\Functions
 */
abstract class Str
{
    /**
     * Pluralize a string
     *
     * @param $singular
     * @param null $plural
     * @return string|null
     */
    public static function pluralize(string $singular, $plural = null): string
    {
        if (is_null($singular)) return null;
        if ($plural !== null) return $plural;

        $last_letter = strtolower($singular[strlen($singular) - 1]);
        switch ($last_letter) {
            case 'y':
                return substr($singular, 0, -1) . 'ies';
            case 's':
                return $singular . 'es';
            default:
                return $singular . 's';
        }
    }

    /**
     * Transform Snake case string to Camel case string
     *
     * @param string $input
     * @param string $separator
     * @return string
     */
    public static function toCamel($input, $separator = '_'): string
    {
        return ucfirst(str_replace($separator, '', ucwords($input, $separator)));
    }

    /**
     * Transform Snake case string to Pascal case string
     *
     * @param string $input
     * @param string $separator
     * @return string
     */
    public static function toPascal(string $input, $separator = '_'): string
    {
        return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
    }

    /**
     * Remove substrings(filters) from a string(string)
     *
     * @param string $string
     * @param array $filters
     * @return string
     */
    public static function filter(string $string, array $filters): string
    {
        foreach ($filters as $filter) {
            $string = strtr($string, $filter, '');
        }
        return $string;
    }

    /**
     * Check if string(haystack) contains the substring(needle)
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) > 0;
    }

}
