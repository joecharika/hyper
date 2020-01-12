<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;

/**
 * Checks if casting an expression to __toString() is allowed by the sandbox.
 *
 * For instance, when there is a simple Print statement, like {{ article }},
 * and if the sandbox is enabled, we need to check that the __toString()
 * method is allowed if 'article' is an object. The same goes for {{ article|upper }}
 * or {{ random(article) }}
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CheckToStringNode extends AbstractExpression
{
    public function __construct(AbstractExpression $expr)
    {
        parent::__construct(['expr' => $expr], [], $expr->getTemplateLine(), $expr->getNodeTag());
    }

    public function compile(Compiler $compiler)
    {
        $expr = $this->getNode('expr');
        $compiler
            ->raw('$this->sandbox->ensureToStringAllowed(')
            ->subcompile($expr)
            ->raw(', ')
            ->repr($expr->getTemplateLine())
            ->raw(', $this->source)')
        ;
    }
}
