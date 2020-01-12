<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

/**
 * @deprecated since version 1.5
 */
class Twig_Extensions_Grammar_Expression extends Twig_Extensions_Grammar
{
    public function __toString()
    {
        return sprintf('<%s>', $this->name);
    }

    public function parse(Twig_Token $token)
    {
        return $this->parser->getExpressionParser()->parseExpression();
    }
}
