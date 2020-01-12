<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

class Twig_Tests_Grammar_OptionalTest extends \PHPUnit\Framework\TestCase
{
    public function testMagicToString()
    {
        $grammar = new Twig_Extensions_Grammar_Optional(new Twig_Extensions_Grammar_Constant('foo'), new Twig_Extensions_Grammar_Number('bar'));
        $this->assertEquals('[foo <bar:number>]', (string) $grammar);
    }
}
