<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Http;


class StatusCode
{
    const __default = self::OK;

    const SWITCHING_PROTOCOLS = 101;
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const MOVED_TEMPORARILY = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 408;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUEST_URI_TOO_LARGE = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;
    const IM_A_TEAPOT = 418;
    const TOO_MANY_REQUESTS = 419;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    public static function getAsArray()
    {
        return [
            101 => 'SWITCHING_PROTOCOLS',

            200 => 'OK',
            201 => 'CREATED',
            202 => 'ACCEPTED',
            203 => 'NON_AUTHORITATIVE_INFORMATION',
            204 => 'NO_CONTENT',
            205 => 'RESET_CONTENT',
            206 => 'PARTIAL_CONTENT',

            300 => 'MULTIPLE_CHOICES',
            301 => 'MOVED_PERMANENTLY',
            302 => 'MOVED_TEMPORARILY',
            303 => 'SEE_OTHER',
            304 => 'NOT_MODIFIED',
            305 => 'USE_PROXY',

            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            402 => 'PAYMENT_REQUIRED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            406 => 'NOT_ACCEPTABLE',
            407 => 'PROXY_AUTHENTICATION_REQUIRED',
            408 => 'REQUEST_TIMEOUT',
            409 => 'CONFLICT',
            410 => 'GONE',
            411 => 'LENGTH_REQUIRED',
            412 => 'PRECONDITION_FAILED',
            413 => 'REQUEST_ENTITY_TOO_LARGE',
            414 => 'REQUEST_URI_TOO_LARGE',
            415 => 'UNSUPPORTED_MEDIA_TYPE',
            416 => 'REQUESTED_RANGE_NOT_SATISFIABLE',
            417 => 'EXPECTATION_FAILED',
            418 => 'IM_A_TEAPOT',
            419 => 'TOO_MANY_REQUESTS',

            500 => 'INTERNAL_SERVER_ERROR',
            501 => 'NOT_IMPLEMENTED',
            502 => 'BAD_GATEWAY',
            503 => 'SERVICE_UNAVAILABLE',
            504 => 'GATEWAY_TIMEOUT',
            505 => 'HTTP_VERSION_NOT_SUPPORTED'
        ];
    }
}