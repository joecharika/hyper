<?php

namespace Hyper\Exception;

class MethodNotAllowedException extends HyperException
{
    public $code = "405",
        $message = "Method not allowed";
}