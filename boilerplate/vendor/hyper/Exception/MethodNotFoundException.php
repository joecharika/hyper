<?php

namespace Hyper\Exception;

class MethodNotFoundException extends HyperException
{
    public $code = "500",
        $message = "The specified method could not be found.";
}
