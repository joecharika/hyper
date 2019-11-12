<?php


namespace Hyper\Functions;


abstract class Debug
{
    /**
     * var_dump a variable and exit immediately
     *
     * @param mixed $var
     */
    public static function dump($var)
    {
        var_dump($var);
        exit(0);
    }

}