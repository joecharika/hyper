<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Utils;


use Hyper\Functions\Arr;

class General
{

    /**
     * Get visitor IP Address
     * @return string
     */
    public static function ipAddress(): string
    {
        return Arr::key($_SERVER, 'HTTP_CLIENT_IP',
            Arr::key($_SERVER, 'HTTP_X_FORWARDED_FOR', $_SERVER['REMOTE_ADDR']));
    }

    /**
     * Get user/visitor browser
     * @return UserBrowser
     */
    public static function browser(): string
    {
        return (new UserBrowser())->getInfoAsString();
    }
}