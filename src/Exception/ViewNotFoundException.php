<?php


namespace Hyper\Exception;


class ViewNotFoundException extends HyperException
{
    public $message = "The specified view cannot be found";
}