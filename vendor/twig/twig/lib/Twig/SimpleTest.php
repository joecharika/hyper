<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\TwigTest;

/*
 * For Twig 1.x compatibility.
 */
class_exists(TwigTest::class);

@trigger_error(sprintf('Using the "Twig_SimpleTest" class is deprecated since Twig version 2.7, use "Twig\TwigTest" instead.'), E_USER_DEPRECATED);

if (false) {
    /** @deprecated since Twig 2.7, use "Twig\TwigTest" instead */
    final class Twig_SimpleTest extends TwigTest
    {
    }
}
