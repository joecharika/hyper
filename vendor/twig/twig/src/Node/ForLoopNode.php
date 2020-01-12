<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Node;

use Twig\Compiler;

/**
 * Internal node used by the for node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ForLoopNode extends Node
{
    public function __construct(int $lineno, string $tag = null)
    {
        parent::__construct([], ['with_loop' => false, 'ifexpr' => false, 'else' => false], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        if ($this->getAttribute('else')) {
            $compiler->write("\$context['_iterated'] = true;\n");
        }

        if ($this->getAttribute('with_loop')) {
            $compiler
                ->write("++\$context['loop']['index0'];\n")
                ->write("++\$context['loop']['index'];\n")
                ->write("\$context['loop']['first'] = false;\n")
            ;

            if (!$this->getAttribute('ifexpr')) {
                $compiler
                    ->write("if (isset(\$context['loop']['length'])) {\n")
                    ->indent()
                    ->write("--\$context['loop']['revindex0'];\n")
                    ->write("--\$context['loop']['revindex'];\n")
                    ->write("\$context['loop']['last'] = 0 === \$context['loop']['revindex0'];\n")
                    ->outdent()
                    ->write("}\n")
                ;
            }
        }
    }
}

class_alias('Twig\Node\ForLoopNode', 'Twig_Node_ForLoop');
