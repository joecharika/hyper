<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node;

use Twig\Compiler;

/**
 * Represents a sandbox node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SandboxNode extends Node
{
    public function __construct(Node $body, int $lineno, string $tag = null)
    {
        parent::__construct(['body' => $body], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("if (!\$alreadySandboxed = \$this->sandbox->isSandboxed()) {\n")
            ->indent()
            ->write("\$this->sandbox->enableSandbox();\n")
            ->outdent()
            ->write("}\n")
            ->subcompile($this->getNode('body'))
            ->write("if (!\$alreadySandboxed) {\n")
            ->indent()
            ->write("\$this->sandbox->disableSandbox();\n")
            ->outdent()
            ->write("}\n")
        ;
    }
}

class_alias('Twig\Node\SandboxNode', 'Twig_Node_Sandbox');
