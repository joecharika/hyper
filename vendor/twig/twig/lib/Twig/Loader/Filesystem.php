<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Loader\FilesystemLoader;

class_exists('Twig\Loader\FilesystemLoader');

@trigger_error(sprintf('Using the "Twig_Loader_Filesystem" class is deprecated since Twig version 2.7, use "Twig\Loader\FilesystemLoader" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Loader\FilesystemLoader" instead */
    class Twig_Loader_Filesystem extends FilesystemLoader
    {
    }
}
