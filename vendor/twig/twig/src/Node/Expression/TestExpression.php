<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node\Expression;

use Twig\Compiler;
use Twig\Node\Node;

class TestExpression extends CallExpression
{
    public function __construct(Node $node, string $name, Node $arguments = null, int $lineno)
    {
        $nodes = ['node' => $node];
        if (null !== $arguments) {
            $nodes['arguments'] = $arguments;
        }

        parent::__construct($nodes, ['name' => $name], $lineno);
    }

    public function compile(Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $test = $compiler->getEnvironment()->getTest($name);

        $this->setAttribute('name', $name);
        $this->setAttribute('type', 'test');
        $this->setAttribute('arguments', $test->getArguments());
        $this->setAttribute('callable', $test->getCallable());
        $this->setAttribute('is_variadic', $test->isVariadic());

        $this->compileCallable($compiler);
    }
}

class_alias('Twig\Node\Expression\TestExpression', 'Twig_Node_Expression_Test');
