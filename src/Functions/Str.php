<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
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

    public static function emptyOrNullReplace($string, $replacement): string
    {
        return empty($string) ? $replacement : $string;
    }

    /**
     * Pluralize a string
     *
     * @param $singular
     * @param null $plural
     * @return string|null
     */
    public static function pluralize(string $singular, $plural = null): ?string
    {
        if (is_null($singular)) return null;
        if ($plural !== null) return $plural;

        $last_letter = strtolower($singular[strlen($singular) - 1]);
        switch ($last_letter) {
            case 'rs':
                return $singular;
            case 'in':
            case 'ch':
            case 's':
            case 'sh':
            case 'x':
            case 'z':
                return $singular . 'es';
            case 'f':
                return substr($singular, 0, -1) . 'ves';
            case 'fe':
                return substr($singular, 0, -2) . 'ves';
            case 'of':
            case 'ief':
            case 'ay':
            case 'ey':
            case 'iy':
            case 'oy':
            case 'uy':
            default:
                return $singular . 's';
            case 'y':
                return substr($singular, 0, -1) . 'ies';
        }
    }

    public static function singular($plural)
    {
        if (self::endsWith($plural, 'ies'))
            return substr($plural, 0, -3) . 'y';
        if (self::endsWith($plural, 'es'))
            return substr($plural, 0, -1);
        if (self::endsWith($plural, 's'))
            return substr($plural, 0, -1);

        return $plural;
    }

    /**
     * Function to check the string is ends with given substring or not
     * @param $string
     * @param $endString
     * @return bool
     */
    public static function endsWith($string, $endString)
    {
        return substr($string, -strlen($endString)) === $endString;
    }

    /**
     * Function to check string starting with given substring
     * @param $string
     * @param $startString
     * @return bool
     */
    public static function startsWith($string, $startString)
    {
        return substr($string, 0, strlen($startString)) === $startString;
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

    public static function toSnake($input, $separator = '_')
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode($separator, $ret);
    }

    /**
     * Transform Snake case string to Pascal case string
     *
     * @param string $input
     * @param array $separators
     * @return string
     */
    public static function toPascal(string $input, ...$separators): string
    {
        $separators = array_merge(['_', '-', ' '], $separators);
        foreach ($separators as $_)
            $input = strtr(ucwords($input, $_), [$_ => '']);
        return lcfirst($input);
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
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function contains($haystack, $needle): bool
    {
        if(empty($needle)) return true;

        return strpos(strtolower($haystack ?? ''), strtolower($needle ?? '')) !== false;
    }

    /**
     * Removes\replaces blank lines from a string
     * @param string $string string to trim blanks
     * @param string $to replacement
     * @return string string without blank\ with replaced blanks
     */
    public static function trimLine(string $string, string $to = ''): string
    {
        $content = explode("\n", $string);
        $result = [];

        foreach ($content as $line) {
            $line = trim($line);
            if (strlen($line) !== 0) {
                array_push($result, $line);
            }
        }
        return implode($to, $result);
    }

}
