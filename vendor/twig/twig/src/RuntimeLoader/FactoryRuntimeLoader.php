<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\RuntimeLoader;

/**
 * Lazy loads the runtime implementations for a Twig element.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class FactoryRuntimeLoader implements RuntimeLoaderInterface
{
    private $map;

    /**
     * @param array $map An array where keys are class names and values factory callables
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    public function load($class)
    {
        if (isset($this->map[$class])) {
            $runtimeFactory = $this->map[$class];

            return $runtimeFactory();
        }
    }
}

class_alias('Twig\RuntimeLoader\FactoryRuntimeLoader', 'Twig_FactoryRuntimeLoader');
