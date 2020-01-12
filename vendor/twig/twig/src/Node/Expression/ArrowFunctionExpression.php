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
 * Represents an arrow function.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ArrowFunctionExpression extends AbstractExpression
{
    public function __construct(AbstractExpression $expr, Node $names, $lineno, $tag = null)
    {
        parent::__construct(['expr' => $expr, 'names' => $names], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->raw('function (')
        ;
        foreach ($this->getNode('names') as $i => $name) {
            if ($i) {
                $compiler->raw(', ');
            }

            $compiler
                ->raw('$__')
                ->raw($name->getAttribute('name'))
                ->raw('__')
            ;
        }
        $compiler
            ->raw(') use ($context, $macros) { ')
        ;
        foreach ($this->getNode('names') as $name) {
            $compiler
                ->raw('$context["')
                ->raw($name->getAttribute('name'))
                ->raw('"] = $__')
                ->raw($name->getAttribute('name'))
                ->raw('__; ')
            ;
        }
        $compiler
            ->raw('return ')
            ->subcompile($this->getNode('expr'))
            ->raw('; }')
        ;
    }
}
