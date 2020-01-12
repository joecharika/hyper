<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Sandbox;

/**
 * Interface that all security policy classes must implements.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface SecurityPolicyInterface
{
    /**
     * @throws SecurityError
     */
    public function checkSecurity($tags, $filters, $functions);

    /**
     * @throws SecurityNotAllowedMethodError
     */
    public function checkMethodAllowed($obj, $method);

    /**
     * @throws SecurityNotAllowedPropertyError
     */
    public function checkPropertyAllowed($obj, $method);
}

class_alias('Twig\Sandbox\SecurityPolicyInterface', 'Twig_Sandbox_SecurityPolicyInterface');
