<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

/**
 * @deprecated since version 1.5
 */
class Twig_Extensions_Grammar_Constant extends Twig_Extensions_Grammar
{
    protected $type;

    public function __construct($name, $type = null)
    {
        $this->name = $name;
        $this->type = null === $type ? Twig_Token::NAME_TYPE : $type;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function parse(Twig_Token $token)
    {
        $this->parser->getStream()->expect($this->type, $this->name);

        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }
}
