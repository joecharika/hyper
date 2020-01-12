<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Extension\SandboxExtension;

class_exists('Twig\Extension\SandboxExtension');

@trigger_error(sprintf('Using the "Twig_Extension_Sandbox" class is deprecated since Twig version 2.7, use "Twig\Extension\SandboxExtension" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Extension\SandboxExtension" instead */
    class Twig_Extension_Sandbox extends SandboxExtension
    {
    }
}
