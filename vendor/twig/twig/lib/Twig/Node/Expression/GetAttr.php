<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Node\Expression\GetAttrExpression;

class_exists('Twig\Node\Expression\GetAttrExpression');

@trigger_error(sprintf('Using the "Twig_Node_Expression_GetAttr" class is deprecated since Twig version 2.7, use "Twig\Node\Expression\GetAttrExpression" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Node\Expression\GetAttrExpression" instead */
    class Twig_Node_Expression_GetAttr extends GetAttrExpression
    {
    }
}