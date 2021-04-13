<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Functions;


use Hyper\Files\Folder;
use function date;
use function fclose;
use function file_exists;
use function fopen;
use function fwrite;
use function mkdir;

abstract class Logger
{
    const INFO = 'INFO', LOG = 'LOG', ERROR = 'ERROR', SUCCESS = 'SUCCESS', CRITICAL = 'CRITICAL', FATAL = 'FATAL', DEBUG = 'DEBUG';

    public static function log($entry, $type = Logger::LOG, $fileName = null, $mode = 'a+')
    {
        if (!\is_string($entry)) $entry = \print_r($entry, true);

        if (!isset($entry)) $entry = 'NULL';

        #Logger directory
        $logDir = Folder::log();

        #Create it if not exists
        if (!file_exists($logDir))
            mkdir($logDir);

        #Today's log file handler
        $file = fopen($logDir . ($fileName ?? 'log_' . date('d_M_Y')), $mode);

        #Log the entry
        fwrite($file, ''
            . ">> [ $type @ " . time() . " ]\n"
            . (string)$entry . "\n\n");

        #Close file handler
        fclose($file);
    }
}