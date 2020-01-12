<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\NameExpression;

/**
 * Represents an import node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ImportNode extends Node
{
    public function __construct(AbstractExpression $expr, AbstractExpression $var, int $lineno, string $tag = null, bool $global = true)
    {
        parent::__construct(['expr' => $expr, 'var' => $var], ['global' => $global], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$macros[')
            ->repr($this->getNode('var')->getAttribute('name'))
            ->raw('] = ')
        ;

        if ($this->getAttribute('global')) {
            $compiler
                ->raw('$this->macros[')
                ->repr($this->getNode('var')->getAttribute('name'))
                ->raw('] = ')
            ;
        }

        if ($this->getNode('expr') instanceof NameExpression && '_self' === $this->getNode('expr')->getAttribute('name')) {
            $compiler->raw('$this');
        } else {
            $compiler
                ->raw('$this->loadTemplate(')
                ->subcompile($this->getNode('expr'))
                ->raw(', ')
                ->repr($this->getTemplateName())
                ->raw(', ')
                ->repr($this->getTemplateLine())
                ->raw(')->unwrap()')
            ;
        }

        $compiler->raw(";\n");
    }
}

class_alias('Twig\Node\ImportNode', 'Twig_Node_Import');
