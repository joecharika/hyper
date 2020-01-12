<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\RuntimeLoader;

use Psr\Container\ContainerInterface;

/**
 * Lazily loads Twig runtime implementations from a PSR-11 container.
 *
 * Note that the runtime services MUST use their class names as identifiers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class ContainerRuntimeLoader implements RuntimeLoaderInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load($class)
    {
        if ($this->container->has($class)) {
            return $this->container->get($class);
        }
    }
}

class_alias('Twig\RuntimeLoader\ContainerRuntimeLoader', 'Twig_ContainerRuntimeLoader');
