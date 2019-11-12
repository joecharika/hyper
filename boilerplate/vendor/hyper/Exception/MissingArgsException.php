<?php

namespace Hyper\Exception;

class MissingArgsException extends HyperException
{
    public $code = "500",
        $message = "Missing arguments.";
}