<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node;

use Twig\Compiler;

/**
 * Represents a spaceless node.
 *
 * It removes spaces between HTML tags.
 *
 * @deprecated since Twig 2.7, to be removed in 3.0
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SpacelessNode extends Node implements NodeOutputInterface
{
    public function __construct(Node $body, int $lineno, string $tag = 'spaceless')
    {
        parent::__construct(['body' => $body], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
        ;
        if ($compiler->getEnvironment()->isDebug()) {
            $compiler->write("ob_start();\n");
        } else {
            $compiler->write("ob_start(function () { return ''; });\n");
        }
        $compiler
            ->subcompile($this->getNode('body'))
            ->write("echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));\n")
        ;
    }
}

class_alias('Twig\Node\SpacelessNode', 'Twig_Node_Spaceless');
