<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Profiler\Dumper\HtmlDumper;

class_exists('Twig\Profiler\Dumper\HtmlDumper');

@trigger_error(sprintf('Using the "Twig_Profiler_Dumper_Html" class is deprecated since Twig version 2.7, use "Twig\Profiler\Dumper\HtmlDumper" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Profiler\Dumper\HtmlDumper" instead */
    class Twig_Profiler_Dumper_Html extends HtmlDumper
    {
    }
}
