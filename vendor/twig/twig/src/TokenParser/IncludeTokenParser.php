<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\TokenParser;

use Twig\Node\IncludeNode;
use Twig\Token;

/**
 * Includes a template.
 *
 *   {% include 'header.html' %}
 *     Body
 *   {% include 'footer.html' %}
 */
class IncludeTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        list($variables, $only, $ignoreMissing) = $this->parseArguments();

        return new IncludeNode($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }

    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

        $ignoreMissing = false;
        if ($stream->nextIf(/* Token::NAME_TYPE */ 5, 'ignore')) {
            $stream->expect(/* Token::NAME_TYPE */ 5, 'missing');

            $ignoreMissing = true;
        }

        $variables = null;
        if ($stream->nextIf(/* Token::NAME_TYPE */ 5, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $only = false;
        if ($stream->nextIf(/* Token::NAME_TYPE */ 5, 'only')) {
            $only = true;
        }

        $stream->expect(/* Token::BLOCK_END_TYPE */ 3);

        return [$variables, $only, $ignoreMissing];
    }

    public function getTag()
    {
        return 'include';
    }
}

class_alias('Twig\TokenParser\IncludeTokenParser', 'Twig_TokenParser_Include');