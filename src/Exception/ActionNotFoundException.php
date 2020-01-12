<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Exception;


class ActionNotFoundException extends HyperException
{
    public $code = "404",
        $message = "The specified action could not be found.";
}