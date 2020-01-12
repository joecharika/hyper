<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node\Expression;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * @internal
 */
final class InlinePrint extends AbstractExpression
{
    public function __construct(Node $node, $lineno)
    {
        parent::__construct(['node' => $node], [], $lineno);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->raw('print (')
            ->subcompile($this->getNode('node'))
            ->raw(')')
        ;
    }
}
