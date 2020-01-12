<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Functions;


use Exception;
use Hyper\Application\HyperApp;
use function array_reverse;

abstract class Debug
{
    /**
     * var_dump a variable and exit immediately in debug mode
     *
     * @param mixed $var
     */
    public static function dump($var)
    {
        $entry = print_r($var, true);

        Logger::log($entry, 'DEBUG');

        if (@HyperApp::$debug ?: false) {
            $output = str_replace(' ', '&nbsp',
                    str_replace("\n", '<br>',
                        preg_replace('/#\d/s', "\n->", ''
                            . implode("\n", array_reverse(explode("\n", (new Exception())->getTraceAsString())))
                        ))) . '<br><br>-> ' . $entry;

            print "<pre>$output</pre>";
            exit(0);
        }
    }

    /**
     * var_dump a variable in debug mode
     *
     * @param mixed $var
     */
    public static function print($var)
    {
        if (@HyperApp::$debug ?: false) {
            $var = isset($var) ? print_r($var, true) : 'NULL';

            $traces = array_reverse((new Exception())->getTrace());
            $trace = array_map(function ($exc) {
                return Arr::key($exc, 'file') . ': ' . Arr::key($exc, 'line');
            }, $traces);
            $str = implode('<br>->', $trace);
            print '<pre>' . '<b><i>' . $str . '</i></b><br>' . $var . '</pre>';
        }
    }

}