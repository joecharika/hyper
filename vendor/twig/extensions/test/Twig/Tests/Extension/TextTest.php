<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

require_once __DIR__.'/../../../../lib/Twig/Extensions/Extension/Text.php';

class Twig_Tests_Extension_TextTest extends \PHPUnit\Framework\TestCase
{
    /** @var TwigEnvironment */
    private $env;

    public function setUp()
    {
        $this->env = $this->getMockBuilder('Twig_Environment')->disableOriginalConstructor()->getMock();
        $this->env
            ->expects($this->any())
            ->method('getCharset')
            ->will($this->returnValue('utf-8'))
        ;
    }

    /**
     * @dataProvider getTruncateTestData
     */
    public function testTruncate($input, $length, $preserve, $separator, $expectedOutput)
    {
        $output = twig_truncate_filter($this->env, $input, $length, $preserve, $separator);
        $this->assertEquals($expectedOutput, $output);
    }

    public function getTruncateTestData()
    {
        return array(
            array('This is a very long sentence.', 2, false, '...', 'Th...'),
            array('This is a very long sentence.', 6, false, '...', 'This i...'),
            array('This is a very long sentence.', 2, true, '...', 'This...'),
            array('This is a very long sentence.', 2, true, '[...]', 'This[...]'),
            array('This is a very long sentence.', 23, false, '...', 'This is a very long sen...'),
            array('This is a very long sentence.', 23, true, '...', 'This is a very long sentence.'),
        );
    }
}
