<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Exception;


class ClassNotFoundException extends HyperException
{
    public function __construct($class)
    {
        $this->message = "The class <b>$class</b> could not be found.";
        $this->code = "500";
        parent::__construct($this->message, $this->code);
    }
}