<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Extension\StagingExtension;

class_exists('Twig\Extension\StagingExtension');

@trigger_error(sprintf('Using the "Twig_Extension_Staging" class is deprecated since Twig version 2.7, use "Twig\Extension\StagingExtension" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Extension\StagingExtension" instead */
    class Twig_Extension_Staging extends StagingExtension
    {
    }
}
