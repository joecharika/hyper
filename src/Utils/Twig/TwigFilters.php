<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Twig;


use Hyper\Utils\UserBrowser;
use Twig\Environment;
use Twig\TwigFilter;

abstract class TwigFilters
{
    public static function attach(Environment &$twig)
    {
        #Cast object to array
        $twig->addFilter(new TwigFilter('toArray', function ($object) {
            return (array)$object;
        }));

        #Cast array to object
        $twig->addFilter(new TwigFilter('toObject', function ($array) {
            return (object)$array;
        }));

        #Cast array to object
        $twig->addFilter(new TwigFilter('isArray', function ($array) {
            return is_array($array);
        }));

        $twig->addFilter(new TwigFilter('toPascal', '\Hyper\Functions\Str::toPascal'));
        $twig->addFilter(new TwigFilter('toCamel', '\Hyper\Functions\Str::toCamel'));
        $twig->addFilter(new TwigFilter('plural', '\Hyper\Functions\Str::pluralize'));
        $twig->addFilter(new TwigFilter('singular', '\Hyper\Functions\Str::singular'));
        $twig->addFilter(new TwigFilter('forge', '\Hyper\Utils\Generator::forgeUrl'));

        $twig->addFilter(new TwigFilter('browser', function ($ua) {
            foreach ((new UserBrowser)->commonBrowsers as $pattern => $name)
                if (preg_match("/" . $pattern . "/i", $ua, $match))
                    return strtolower($pattern);
            return 'hashtag';
        }));

        $twig->addFilter(new TwigFilter('take', function ($input, $length) {
            if (is_array($input)) {
                return array_slice($input, 0, $length);
            } elseif (is_string($input)) {
                return substr($input, 0, $length) . (strlen($input) > $length ? '...' : '');
            }
            return '-';
        }));


        $twig->addFilter(new TwigFilter('where', function ($array, $closure) {
            if (is_array($array)) {
                return array_filter($array, $closure);
            }
            return $array;
        }));
    }
}