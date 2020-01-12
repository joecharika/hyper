<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Extension;

use Twig\Environment;

/**
 * Enables usage of the deprecated Twig\Extension\AbstractExtension::initRuntime() method.
 *
 * Explicitly implement this interface if you really need to implement the
 * deprecated initRuntime() method in your extensions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @deprecated since Twig 2.7, to be removed in 3.0
 */
interface InitRuntimeInterface
{
    /**
     * Initializes the runtime environment.
     *
     * This is where you can load some file that contains filter functions for instance.
     */
    public function initRuntime(Environment $environment);
}

class_alias('Twig\Extension\InitRuntimeInterface', 'Twig_Extension_InitRuntimeInterface');
