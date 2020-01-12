<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Cache\NullCache;

class_exists('Twig\Cache\NullCache');

@trigger_error(sprintf('Using the "Twig_Cache_Null" class is deprecated since Twig version 2.7, use "Twig\Cache\NullCache" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Cache\NullCache" instead */
    class Twig_Cache_Null extends NullCache
    {
    }
}
