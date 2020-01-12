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
 * Checks if a number is even.
 *
 *  {{ var is even }}
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class EvenTest extends TestExpression
{
    public function compile(Compiler $compiler)
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('node'))
            ->raw(' % 2 == 0')
            ->raw(')')
        ;
    }
}

class_alias('Twig\Node\Expression\Test\EvenTest', 'Twig_Node_Expression_Test_Even');
