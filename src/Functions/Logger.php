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
use function str_repeat;

abstract class Logger
{
    const INFO = 'INFO', LOG = 'LOG', ERROR = 'ERROR', SUCCESS = 'SUCCESS', CRITICAL = 'CRITICAL', FATAL = 'FATAL', DEBUG = 'DEBUG';

    public static function log(string $entry, $type = Logger::LOG, $fileName = null, $mode = 'a+')
    {
        if (!isset($entry)) $entry = 'NULL';

        #Logger directory
        $logDir = Folder::log();

        #Create it if not exists
        if (!file_exists($logDir))
            mkdir($logDir);

        #Today's log file handler
        $file = fopen($logDir . ($fileName ?? 'LOG - ' . date('d M_Y')), $mode);

        #Log the entry
        fwrite($file, ''
            . str_repeat('=', 20)
            . "[ $type @ " . date("h:i:sa") . ' ]'
            . str_repeat('=', 20) . "\n"
            . (string)$entry . "\n\n");

        #Close file handler
        fclose($file);
    }
}