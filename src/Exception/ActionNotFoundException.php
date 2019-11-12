<?php


namespace Hyper\Exception;


class ActionNotFoundException extends HyperException
{
    public $code = "404",
        $message = "The specified action could not be found.";
}