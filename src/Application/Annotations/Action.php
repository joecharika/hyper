<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Annotations;


/**
 * Annotation Action
 * Used to mark a method as an http action
 * @package Hyper\Annotations
 * @Annotation
 */
class action
{
    /**
     * Action constructor.
     * @param bool $isAction
     */
    public function __construct(bool $isAction = true)
    {
    }
}