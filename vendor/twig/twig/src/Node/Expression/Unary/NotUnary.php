<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node\Expression\Unary;

use Twig\Compiler;

class NotUnary extends AbstractUnary
{
    public function operator(Compiler $compiler)
    {
        $compiler->raw('!');
    }
}

class_alias('Twig\Node\Expression\Unary\NotUnary', 'Twig_Node_Expression_Unary_Not');
