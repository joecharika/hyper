<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

/**
 * @deprecated since version 1.5
 */
class Twig_Extensions_Grammar_Body extends Twig_Extensions_Grammar
{
    protected $end;

    public function __construct($name, $end = null)
    {
        parent::__construct($name);

        $this->end = null === $end ? 'end'.$name : $end;
    }

    public function __toString()
    {
        return sprintf('<%s:body>', $this->name);
    }

    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return $this->parser->subparse(array($this, 'decideBlockEnd'), true);
    }

    public function decideBlockEnd(Twig_Token $token)
    {
        return $token->test($this->end);
    }
}
