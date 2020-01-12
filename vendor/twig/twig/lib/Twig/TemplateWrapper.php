<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\TemplateWrapper;

class_exists('Twig\TemplateWrapper');

@trigger_error(sprintf('Using the "Twig_TemplateWrapper" class is deprecated since Twig version 2.7, use "Twig\TemplateWrapper" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\TemplateWrapper" instead */
    class Twig_TemplateWrapper extends TemplateWrapper
    {
    }
}
