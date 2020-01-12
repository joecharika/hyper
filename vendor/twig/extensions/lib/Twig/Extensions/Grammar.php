<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

/**
 * @deprecated since version 1.5
 */
abstract class Twig_Extensions_Grammar implements Twig_Extensions_GrammarInterface
{
    protected $name;
    protected $parser;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param Twig_Parser $parser
     */
    public function setParser(Twig_Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
