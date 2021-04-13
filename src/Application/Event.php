<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application;


/**
 * Class Event
 * Event model
 * @package Hyper\Application
 */
class Event
{
    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $data;

    /**
     * Event constructor.
     * @param $name
     * @param $data
     */
    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }
}