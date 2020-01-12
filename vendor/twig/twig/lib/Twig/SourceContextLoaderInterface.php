<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Loader\SourceContextLoaderInterface;

class_exists('Twig\Loader\SourceContextLoaderInterface');

@trigger_error(sprintf('Using the "Twig_SourceContextLoaderInterface" class is deprecated since Twig version 2.7, use "Twig\Loader\SourceContextLoaderInterface" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Loader\SourceContextLoaderInterface" instead */
    class Twig_SourceContextLoaderInterface extends SourceContextLoaderInterface
    {
    }
}
