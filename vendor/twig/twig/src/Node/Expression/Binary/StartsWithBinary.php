<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node\Expression\Binary;

use Twig\Compiler;

class StartsWithBinary extends AbstractBinary
{
    public function compile(Compiler $compiler)
    {
        $left = $compiler->getVarName();
        $right = $compiler->getVarName();
        $compiler
            ->raw(sprintf('(is_string($%s = ', $left))
            ->subcompile($this->getNode('left'))
            ->raw(sprintf(') && is_string($%s = ', $right))
            ->subcompile($this->getNode('right'))
            ->raw(sprintf(') && (\'\' === $%2$s || 0 === strpos($%1$s, $%2$s)))', $left, $right))
        ;
    }

    public function operator(Compiler $compiler)
    {
        return $compiler->raw('');
    }
}

class_alias('Twig\Node\Expression\Binary\StartsWithBinary', 'Twig_Node_Expression_Binary_StartsWith');
