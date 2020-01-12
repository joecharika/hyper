<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\NodeVisitor\AbstractNodeVisitor;

class_exists('Twig\NodeVisitor\AbstractNodeVisitor');

@trigger_error(sprintf('Using the "Twig_BaseNodeVisitor" class is deprecated since Twig version 2.7, use "Twig\NodeVisitor\AbstractNodeVisitor" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\NodeVisitor\AbstractNodeVisitor" instead */
    class Twig_BaseNodeVisitor extends AbstractNodeVisitor
    {
    }
}
