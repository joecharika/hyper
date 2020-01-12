<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Node\EmbedNode;

class_exists('Twig\Node\EmbedNode');

@trigger_error(sprintf('Using the "Twig_Node_Embed" class is deprecated since Twig version 2.7, use "Twig\Node\EmbedNode" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Node\EmbedNode" instead */
    class Twig_Node_Embed extends EmbedNode
    {
    }
}
