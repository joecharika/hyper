<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Profiler\NodeVisitor\ProfilerNodeVisitor;

class_exists('Twig\Profiler\NodeVisitor\ProfilerNodeVisitor');

@trigger_error(sprintf('Using the "Twig_Profiler_NodeVisitor_Profiler" class is deprecated since Twig version 2.7, use "Twig\Profiler\NodeVisitor\ProfilerNodeVisitor" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Profiler\NodeVisitor\ProfilerNodeVisitor" instead */
    class Twig_Profiler_NodeVisitor_Profiler extends ProfilerNodeVisitor
    {
    }
}
