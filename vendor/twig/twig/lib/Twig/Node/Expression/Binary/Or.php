<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Node\Expression\Binary\OrBinary;

class_exists('Twig\Node\Expression\Binary\OrBinary');

@trigger_error(sprintf('Using the "Twig_Node_Expression_Binary_Or" class is deprecated since Twig version 2.7, use "Twig\Node\Expression\Binary\OrBinary" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Node\Expression\Binary\OrBinary" instead */
    class Twig_Node_Expression_Binary_Or extends OrBinary
    {
    }
}
