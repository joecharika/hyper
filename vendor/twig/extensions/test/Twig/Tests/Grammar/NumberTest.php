<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

class Twig_Tests_Grammar_NumberTest extends \PHPUnit\Framework\TestCase
{
    public function testMagicToString()
    {
        $grammar = new Twig_Extensions_Grammar_Number('foo');
        $this->assertEquals('<foo:number>', (string) $grammar);
    }
}
