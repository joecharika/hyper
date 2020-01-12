<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node\Expression\Binary;

use Twig\Compiler;

class GreaterEqualBinary extends AbstractBinary
{
    public function operator(Compiler $compiler)
    {
        return $compiler->raw('>=');
    }
}

class_alias('Twig\Node\Expression\Binary\GreaterEqualBinary', 'Twig_Node_Expression_Binary_GreaterEqual');
