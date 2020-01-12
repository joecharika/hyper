<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node\Expression;

use Twig\Compiler;

class VariadicExpression extends ArrayExpression
{
    public function compile(Compiler $compiler)
    {
        $compiler->raw('...');

        parent::compile($compiler);
    }
}
