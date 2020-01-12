<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\ExpressionParser;

class_exists('Twig\ExpressionParser');

@trigger_error(sprintf('Using the "Twig_ExpressionParser" class is deprecated since Twig version 2.7, use "Twig\ExpressionParser" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\ExpressionParser" instead */
    class Twig_ExpressionParser extends ExpressionParser
    {
    }
}
