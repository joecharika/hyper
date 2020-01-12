<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\RuntimeLoader;

/**
 * Creates runtime implementations for Twig elements (filters/functions/tests).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface RuntimeLoaderInterface
{
    /**
     * Creates the runtime implementation of a Twig element (filter/function/test).
     *
     * @param string $class A runtime class
     *
     * @return object|null The runtime instance or null if the loader does not know how to create the runtime for this class
     */
    public function load($class);
}

class_alias('Twig\RuntimeLoader\RuntimeLoaderInterface', 'Twig_RuntimeLoaderInterface');
