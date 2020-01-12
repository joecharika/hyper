<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Loader;

/**
 * Empty interface for Twig 1.x compatibility.
 */
interface SourceContextLoaderInterface extends LoaderInterface
{
}

class_alias('Twig\Loader\SourceContextLoaderInterface', 'Twig_SourceContextLoaderInterface');
