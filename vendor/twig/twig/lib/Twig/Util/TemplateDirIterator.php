<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Util\TemplateDirIterator;

class_exists('Twig\Util\TemplateDirIterator');

@trigger_error(sprintf('Using the "Twig_Util_TemplateDirIterator" class is deprecated since Twig version 2.7, use "Twig\Util\TemplateDirIterator" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Util\TemplateDirIterator" instead */
    class Twig_Util_TemplateDirIterator extends TemplateDirIterator
    {
    }
}
