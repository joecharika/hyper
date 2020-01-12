<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Http;


use Hyper\Functions\Arr;

class Cookie
{
    /**
     * Create a new cookie list.
     * @param mixed $cookies The string or list of cookies to parse or set.
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     */
    function __construct($cookies = array(), $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        $this->addCookies($cookies, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * (Re)set the cookies.
     * @param array $cookies Add cookies of this array of form ["name" => "value"].
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     */
    function addCookies(array $cookies, $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        foreach ($cookies as $key => $value) {
            $this->addCookie($key, $value, $expire, $path, $domain, $secure, $httponly);
        }
    }

    /**
     * Add a cookie.
     * See http\Cookie::setCookie() and http\Cookie::addCookies().
     *
     * @param string $cookieName The key of the cookie.
     * @param string $cookieValue The value of the cookie.
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
    function addCookie(string $cookieName, string $cookieValue, $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false)
    {
        $expire = $expire ?: time() + 60 * 60 * 24 * 1;
        return setcookie($cookieName, $cookieValue, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * String cast handler. Alias of http\Cookie::toString().
     *
     * @return string the cookie(s) represented as string.
     */
    function __toString()
    {
        return Arr::spread($this->getCookies(), true);
    }

    /**
     * Get the list of cookies.
     * See http\Cookie::setCookies().
     *
     * @return array the list of cookies of form ["name" => "value"].
     */
    function getCookies()
    {
        return $_COOKIE;
    }

    function removeCookie(string $cookieName)
    {
        return setcookie($cookieName, '', 0, '/');
    }

    /**
     * Retrieve a specific cookie value.
     *
     * @param string $cookie_name The key of the cookie to look up.
     * @return string|NULL string the cookie value.
     *         or NULL if $cookie_name could not be found.
     */
    function getCookie(string $cookie_name)
    {
        return Arr::key($_COOKIE, $cookie_name, '');
    }

    /**
     * Retrieve the string representation of the cookie list.
     *
     * @return string the cookie list as string.
     */
    function toString()
    {
        return Arr::spread($_COOKIE, true, "\n", " => ");
    }
}

