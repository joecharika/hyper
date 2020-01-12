<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Exception;


use Exception;

class HyperHttpException extends HyperException
{
    public static function notFound($message = "Not found"): Exception
    {
        return self::get($message, "404");
    }

    private static function get($message, $code)
    {
        $exc = new HyperHttpException($message, $code);
        $var = @debug_backtrace()[1];

        $exc->line = @$var['line'];
        $exc->file = @$var['file'];

        return $exc;
    }

    public static function badRequest(): Exception
    {
        return self::get("Bad request", "400.5");
    }

    public static function notAuthorised(): Exception
    {
        return self::get("Not authorised", "403");
    }
}

class HttpResponse extends HyperHttpException
{
}