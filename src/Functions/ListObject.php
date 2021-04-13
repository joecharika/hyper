<?php


namespace Hyper\Functions;


use ArrayObject;
use Closure;
use function array_filter;
use function array_map;

class ListObject extends ArrayObject
{
    private array $list;

    public function __construct($input = [], $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct($this->list = $input, $flags, $iterator_class);
    }

    public function map(Closure $callable)
    {
        return new ListObject(array_map($callable, $this->list));
    }

    public function filter(Closure $callable)
    {
        return new ListObject(array_filter($this->list, $callable));
    }

    public function values(){
        return new ListObject(array_values($this->list));
    }
}