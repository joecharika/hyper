<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Lexer;

class_exists('Twig\Lexer');

@trigger_error(sprintf('Using the "Twig_Lexer" class is deprecated since Twig version 2.7, use "Twig\Lexer" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Lexer" instead */
    class Twig_Lexer extends Lexer
    {
    }
}
