<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\FileExtensionEscapingStrategy;

class_exists('Twig\FileExtensionEscapingStrategy');

@trigger_error(sprintf('Using the "Twig_FileExtensionEscapingStrategy" class is deprecated since Twig version 2.7, use "Twig\FileExtensionEscapingStrategy" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\FileExtensionEscapingStrategy" instead */
    class Twig_FileExtensionEscapingStrategy extends FileExtensionEscapingStrategy
    {
    }
}
