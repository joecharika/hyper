<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Cache;

/**
 * Implements a no-cache strategy.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class NullCache implements CacheInterface
{
    public function generateKey($name, $className)
    {
        return '';
    }

    public function write($key, $content)
    {
    }

    public function load($key)
    {
    }

    public function getTimestamp($key)
    {
        return 0;
    }
}

class_alias('Twig\Cache\NullCache', 'Twig_Cache_Null');
