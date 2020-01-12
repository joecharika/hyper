<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node\Expression;

use Twig\Compiler;

class AssignNameExpression extends NameExpression
{
    public function compile(Compiler $compiler)
    {
        $compiler
            ->raw('$context[')
            ->string($this->getAttribute('name'))
            ->raw(']')
        ;
    }
}

class_alias('Twig\Node\Expression\AssignNameExpression', 'Twig_Node_Expression_AssignName');
