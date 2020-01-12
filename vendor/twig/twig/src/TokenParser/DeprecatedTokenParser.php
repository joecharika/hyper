<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\TokenParser;

use Twig\Node\DeprecatedNode;
use Twig\Token;

/**
 * Deprecates a section of a template.
 *
 *    {% deprecated 'The "base.twig" template is deprecated, use "layout.twig" instead.' %}
 *    {% extends 'layout.html.twig' %}
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 *
 * @final
 */
class DeprecatedTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new DeprecatedNode($expr, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'deprecated';
    }
}

class_alias('Twig\TokenParser\DeprecatedTokenParser', 'Twig_TokenParser_Deprecated');
