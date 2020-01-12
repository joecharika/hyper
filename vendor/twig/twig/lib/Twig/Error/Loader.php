<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Error\LoaderError;

class_exists('Twig\Error\LoaderError');

@trigger_error(sprintf('Using the "Twig_Error_Loader" class is deprecated since Twig version 2.7, use "Twig\Error\LoaderError" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Error\LoaderError" instead */
    class Twig_Error_Loader extends LoaderError
    {
    }
}
