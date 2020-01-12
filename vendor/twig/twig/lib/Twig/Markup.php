<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Markup;

class_exists('Twig\Markup');

@trigger_error(sprintf('Using the "Twig_Markup" class is deprecated since Twig version 2.7, use "Twig\Markup" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Markup" instead */
    class Twig_Markup extends Markup
    {
    }
}
