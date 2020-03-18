<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Utils;


class Generator
{
    /**
     * Generate new token
     * @param string $start Starting key
     * @return string
     */
    public static function token($start = '__')
    {
        return $start . uniqid() . uniqid() . uniqid() . uniqid() . date('jNWto.his');
    }

    /**
     * @param $string|null
     * @return string|null
     */
    public static function forgeUrl($string)
    {
        return isset($string)
            ? strtr(strtolower($string), [' ' => '-', '.' => ''])
            : null;
    }
}