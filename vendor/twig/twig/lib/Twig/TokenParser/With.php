<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\TokenParser\WithTokenParser;

class_exists('Twig\TokenParser\WithTokenParser');

@trigger_error(sprintf('Using the "Twig_TokenParser_With" class is deprecated since Twig version 2.7, use "Twig\TokenParser\WithTokenParser" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\TokenParser\WithTokenParser" instead */
    class Twig_TokenParser_With extends WithTokenParser
    {
    }
}
