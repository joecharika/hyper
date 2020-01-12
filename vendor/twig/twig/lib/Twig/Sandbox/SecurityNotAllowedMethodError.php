<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

use Twig\Sandbox\SecurityNotAllowedMethodError;

class_exists('Twig\Sandbox\SecurityNotAllowedMethodError');

@trigger_error(sprintf('Using the "Twig_Sandbox_SecurityNotAllowedMethodError" class is deprecated since Twig version 2.7, use "Twig\Sandbox\SecurityNotAllowedMethodError" instead.'), E_USER_DEPRECATED);

if (\false) {
    /** @deprecated since Twig 2.7, use "Twig\Sandbox\SecurityNotAllowedMethodError" instead */
    class Twig_Sandbox_SecurityNotAllowedMethodError extends SecurityNotAllowedMethodError
    {
    }
}
