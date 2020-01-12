<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Annotations;


/**
 * Annotation Authorize
 * Used to mark a controller or an action as one that requires authorization
 * @package Hyper\Annotations
 * @Annotation
 */
class authorize
{
    /**
     * Authorize constructor.
     * @param array $roles
     * @param string $redirect
     */
    public function __construct(array $roles = [], $redirect = '/auth/login')
    {
    }
}