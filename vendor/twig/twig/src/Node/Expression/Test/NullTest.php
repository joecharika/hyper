<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node\Expression\Test;

use Twig\Compiler;
use Twig\Node\Expression\TestExpression;

/**
 * Checks that a variable is null.
 *
 *  {{ var is none }}
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class NullTest extends TestExpression
{
    public function compile(Compiler $compiler)
    {
        $compiler
            ->raw('(null === ')
            ->subcompile($this->getNode('node'))
            ->raw(')')
        ;
    }
}

class_alias('Twig\Node\Expression\Test\NullTest', 'Twig_Node_Expression_Test_Null');
