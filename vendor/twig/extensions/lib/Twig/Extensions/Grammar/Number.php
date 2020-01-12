<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

/**
 * @deprecated since version 1.5
 */
class Twig_Extensions_Grammar_Number extends Twig_Extensions_Grammar
{
    public function __toString()
    {
        return sprintf('<%s:number>', $this->name);
    }

    public function parse(Twig_Token $token)
    {
        $this->parser->getStream()->expect(Twig_Token::NUMBER_TYPE);

        return new Twig_Node_Expression_Constant($token->getValue(), $token->getLine());
    }
}
