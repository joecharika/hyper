<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

class SimpleTokenParser extends Twig_Extensions_SimpleTokenParser
{
    protected $tag;
    protected $grammar;

    public function __construct($tag, $grammar)
    {
        $this->tag = $tag;
        $this->grammar = $grammar;
    }

    public function getGrammar()
    {
        return $this->grammar;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getNode(array $values, $line)
    {
        $nodes = array();
        $nodes[] = new Twig_Node_Print(new Twig_Node_Expression_Constant('|', $line), $line);
        foreach ($values as $value) {
            if ($value instanceof Twig_Node) {
                if ($value instanceof Twig_Node_Expression_Array) {
                    $nodes[] = new Twig_Node_Print(new Twig_Node_Expression_Function('dump', $value, $line), $line);
                } else {
                    $nodes[] = new Twig_Node_Print($value, $line);
                }
            } else {
                $nodes[] = new Twig_Node_Print(new Twig_Node_Expression_Constant($value, $line), $line);
            }
            $nodes[] = new Twig_Node_Print(new Twig_Node_Expression_Constant('|', $line), $line);
        }

        return new Twig_Node($nodes);
    }
}
