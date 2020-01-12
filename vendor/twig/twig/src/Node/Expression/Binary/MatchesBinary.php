<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node\Expression\Binary;

use Twig\Compiler;

class MatchesBinary extends AbstractBinary
{
    public function compile(Compiler $compiler)
    {
        $compiler
            ->raw('preg_match(')
            ->subcompile($this->getNode('right'))
            ->raw(', ')
            ->subcompile($this->getNode('left'))
            ->raw(')')
        ;
    }

    public function operator(Compiler $compiler)
    {
        return $compiler->raw('');
    }
}

class_alias('Twig\Node\Expression\Binary\MatchesBinary', 'Twig_Node_Expression_Binary_Matches');
