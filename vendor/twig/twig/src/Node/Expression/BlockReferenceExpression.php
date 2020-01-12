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
 * Represents a block call node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class BlockReferenceExpression extends AbstractExpression
{
    public function __construct(Node $name, Node $template = null, int $lineno, string $tag = null)
    {
        $nodes = ['name' => $name];
        if (null !== $template) {
            $nodes['template'] = $template;
        }

        parent::__construct($nodes, ['is_defined_test' => false, 'output' => false], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        if ($this->getAttribute('is_defined_test')) {
            $this->compileTemplateCall($compiler, 'hasBlock');
        } else {
            if ($this->getAttribute('output')) {
                $compiler->addDebugInfo($this);

                $this
                    ->compileTemplateCall($compiler, 'displayBlock')
                    ->raw(";\n");
            } else {
                $this->compileTemplateCall($compiler, 'renderBlock');
            }
        }
    }

    private function compileTemplateCall(Compiler $compiler, string $method): Compiler
    {
        if (!$this->hasNode('template')) {
            $compiler->write('$this');
        } else {
            $compiler
                ->write('$this->loadTemplate(')
                ->subcompile($this->getNode('template'))
                ->raw(', ')
                ->repr($this->getTemplateName())
                ->raw(', ')
                ->repr($this->getTemplateLine())
                ->raw(')')
            ;
        }

        $compiler->raw(sprintf('->%s', $method));

        return $this->compileBlockArguments($compiler);
    }

    private function compileBlockArguments(Compiler $compiler): Compiler
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('name'))
            ->raw(', $context');

        if (!$this->hasNode('template')) {
            $compiler->raw(', $blocks');
        }

        return $compiler->raw(')');
    }
}

class_alias('Twig\Node\Expression\BlockReferenceExpression', 'Twig_Node_Expression_BlockReference');
