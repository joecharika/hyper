<?php


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