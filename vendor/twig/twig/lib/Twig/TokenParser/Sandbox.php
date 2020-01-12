<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\TokenParser\SandboxTokenParser;

class_exists('Twig\TokenParser\SandboxTokenParser');

@trigger_error(sprintf('Using the "Twig_TokenParser_Sandbox" class is deprecated since Twig version 2.7, use "Twig\TokenParser\SandboxTokenParser" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\TokenParser\SandboxTokenParser" instead */
    class Twig_TokenParser_Sandbox extends SandboxTokenParser
    {
    }
}
