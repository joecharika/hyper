<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Token;

class_exists('Twig\Token');

@trigger_error(sprintf('Using the "Twig_Token" class is deprecated since Twig version 2.7, use "Twig\Token" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Token" instead */
    class Twig_Token extends Token
    {
    }
}
