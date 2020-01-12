<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\ExtensionSet;

class_exists('Twig\ExtensionSet');

@trigger_error(sprintf('Using the "Twig_ExtensionSet" class is deprecated since Twig version 2.7, use "Twig\ExtensionSet" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\ExtensionSet" instead */
    class Twig_ExtensionSet extends ExtensionSet
    {
    }
}
