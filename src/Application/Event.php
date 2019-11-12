<?php


namespace hyper\Application;


class Event
{
    public $name, $data;

    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }
}