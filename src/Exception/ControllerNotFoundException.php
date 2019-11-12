<?php


namespace Hyper\Exception;


class ControllerNotFoundException extends HyperException
{
    public $code = "404", $message = "The specified controller could not be found.";
}