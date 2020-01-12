<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\TokenParser;

use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;

/**
 * Extends a template by another one.
 *
 *  {% extends "base.html" %}
 */
final class ExtendsTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();

        if ($this->parser->peekBlockStack()) {
            throw new SyntaxError('Cannot use "extend" in a block.', $token->getLine(), $stream->getSourceContext());
        } elseif (!$this->parser->isMainScope()) {
            throw new SyntaxError('Cannot use "extend" in a macro.', $token->getLine(), $stream->getSourceContext());
        }

        if (null !== $this->parser->getParent()) {
            throw new SyntaxError('Multiple extends tags are forbidden.', $token->getLine(), $stream->getSourceContext());
        }
        $this->parser->setParent($this->parser->getExpressionParser()->parseExpression());

        $stream->expect(Token::BLOCK_END_TYPE);

        return new Node();
    }

    public function getTag()
    {
        return 'extends';
    }
}

class_alias('Twig\TokenParser\ExtendsTokenParser', 'Twig_TokenParser_Extends');
