<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Extension\StringLoaderExtension;

class_exists('Twig\Extension\StringLoaderExtension');

@trigger_error(sprintf('Using the "Twig_Extension_StringLoader" class is deprecated since Twig version 2.7, use "Twig\Extension\StringLoaderExtension" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Extension\StringLoaderExtension" instead */
    class Twig_Extension_StringLoader extends StringLoaderExtension
    {
    }
}
