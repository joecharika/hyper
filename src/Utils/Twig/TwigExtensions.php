<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Utils\Twig;


use Hyper\Utils\UserBrowser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use function call_user_func;
use function is_object;
use function is_string;
use function str_split;

class TwigExtensions extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('toArray', function ($object) {
                if (is_string($object)) return str_split($object);
                if (is_object($object)) return (array)$object;
                return $object;
            }),
            new TwigFilter('shuffle', [TwigFunctions::class, 'shuffle']),
            new TwigFilter('toObject', function ($array) {
                return (object)$array;
            }),
            new TwigFilter('isArray', function ($array) {
                return is_array($array);
            }),
            new TwigFilter('native', [TwigFunctions::class, 'native']),
            new TwigFilter('toPascal', '\Hyper\Functions\Str::toPascal'),
            new TwigFilter('toCamel', '\Hyper\Functions\Str::toCamel'),
            new TwigFilter('plural', '\Hyper\Functions\Str::pluralize'),
            new TwigFilter('singular', '\Hyper\Functions\Str::singular'),
            new TwigFilter('contains', '\Hyper\Functions\Str::contains'),
            new TwigFilter('slug', '\Hyper\Utils\Generator::forgeUrl'),

            new TwigFilter('browser', function ($ua) {
                foreach ((new UserBrowser)->commonBrowsers as $pattern => $name)
                    if (preg_match("/" . $pattern . "/i", $ua, $match))
                        return strtolower($pattern);
                return 'hashtag';
            }),

            new TwigFilter('take', function ($input, $length, $skip = 0) {
                if (is_array($input)) {
                    return array_slice($input, $skip, $length);
                } elseif (is_string($input)) {
                    return substr($input, $skip, $length) . (strlen($input) > $length ? '...' : '');
                }
                return '-';
            }),

            new TwigFilter('where', function ($array, $closure) {
                if (is_array($array)) {
                    return array_filter($array, $closure);
                }
                return $array;
            }),

            new TwigFilter('sum', function ($array, $closure) {
                $total = 0;

                if (is_array($array))
                    foreach ($array as $item) {
                        $total += call_user_func($closure, $item);
                    }

                return $total;
            }),
        ];
    }

    public function getFunctions(): array
    {
        return TwigFunctions::getFunctions();
    }
}