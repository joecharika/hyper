<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Node\NodeOutputInterface;

class_exists('Twig\Node\NodeOutputInterface');

@trigger_error(sprintf('Using the "Twig_NodeOutputInterface" class is deprecated since Twig version 2.7, use "Twig\Node\NodeOutputInterface" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Node\NodeOutputInterface" instead */
    class Twig_NodeOutputInterface extends NodeOutputInterface
    {
    }
}
