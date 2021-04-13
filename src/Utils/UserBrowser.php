<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Utils;


use Hyper\Functions\Str;

class UserBrowser
{
    public $commonBrowsers = [
        'Trident\/7.0' => 'Internet Explorer 11',
        'Vivaldi' => 'Vivaldi',
        'Brave' => 'Brave',
        'Edge' => 'Microsoft Edge',
        'Beamrise' => 'Beamrise',
        'Opera' => 'Opera',
        'OPR' => 'Opera',
        'Shiira' => 'Shiira',
        'Chimera' => 'Chimera',
        'Phoenix' => 'Phoenix',
        'Firebird' => 'Firebird',
        'Camino' => 'Camino',
        'Netscape' => 'Netscape',
        'OmniWeb' => 'OmniWeb',
        'Konqueror' => 'Konqueror',
        'icab' => 'iCab',
        'Lynx' => 'Lynx',
        'Links' => 'Links',
        'hotjava' => 'HotJava',
        'amaya' => 'Amaya',
        'IBrowse' => 'IBrowse',
        'iTunes' => 'iTunes',
        'Silk' => 'Silk',
        'Dillo' => 'Dillo',
        'Maxthon' => 'Maxthon',
        'Arora' => 'Arora',
        'Galeon' => 'Galeon',
        'Iceape' => 'Iceape',
        'Iceweasel' => 'Iceweasel',
        'Midori' => 'Midori',
        'QupZilla' => 'QupZilla',
        'Namoroka' => 'Namoroka',
        'NetSurf' => 'NetSurf',
        'BOLT' => 'BOLT',
        'EudoraWeb' => 'EudoraWeb',
        'shadowfox' => 'ShadowFox',
        'Swiftfox' => 'Swiftfox',
        'Uzbl' => 'Uzbl',
        'UCBrowser' => 'UCBrowser',
        'Kindle' => 'Kindle',
        'wOSBrowser' => 'wOSBrowser',
        'Epiphany' => 'Epiphany',
        'SeaMonkey' => 'SeaMonkey',
        'Avant Browser' => 'Avant Browser',
        'Firefox' => 'Firefox',
        'Chrome' => 'Google Chrome',
        'MSIE' => 'Internet Explorer',
        'Internet Explorer' => 'Internet Explorer',
        'Safari' => 'Safari',
        'Mozilla' => 'Mozilla'
    ],
        $commonPlatforms = [
        'windows' => 'Windows',
        'iPad' => 'iPad',
        'iPod' => 'iPod',
        'iPhone' => 'iPhone',
        'mac' => 'Apple',
        'android' => 'Android',
        'linux' => 'Linux',
        'Nokia' => 'Nokia',
        'BlackBerry' => 'BlackBerry',
        'FreeBSD' => 'FreeBSD',
        'OpenBSD' => 'OpenBSD',
        'NetBSD' => 'NetBSD',
        'UNIX' => 'UNIX',
        'DragonFly' => 'DragonFlyBSD',
        'OpenSolaris' => 'OpenSolaris',
        'SunOS' => 'SunOS',
        'OS\/2' => 'OS/2',
        'BeOS' => 'BeOS',
        'win' => 'Windows',
        'Dillo' => 'Linux',
        'PalmOS' => 'PalmOS',
        'RebelMouse' => 'RebelMouse'
    ];
    private $userAgent,
        $name,
        $version,
        $platform;

    function __construct($userAgent = '')
    {
        $this->userAgent = empty($userAgent) ? (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT')) : $userAgent;
        $this->detectBrowser();
        $this->detectPlatform();
    }

    function detectBrowser()
    {
        foreach ($this->commonBrowsers as $pattern => $name) {
            if (preg_match("/" . $pattern . "/i", $this->userAgent, $match)) {
                $this->name = $name;
                # finally get the correct version number
                $known = array('Version', $pattern, 'other');
                $pattern_version = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

                if (!preg_match_all($pattern_version, $this->userAgent, $matches)) {
                    # we have no matching number just continue
                }
                # see how many we have
                $i = count($matches['browser']);
                if ($i != 1) {
                    #we will have two since we are not using 'other' argument yet
                    #see if version is before or after the name
                    if (strripos($this->userAgent, "Version") < strripos($this->userAgent, $pattern)) {
                        @$this->version = $matches['version'][0];
                    } else {
                        @$this->version = $matches['version'][1];
                    }
                } else {
                    $this->version = $matches['version'][0];
                }
                break;
            }
        }
    }

    function detectPlatform()
    {
        foreach ($this->commonPlatforms as $key => $platform) {
            if (Str::contains($this->userAgent, $key)) {
                $this->platform = $platform;
                break;
            }
        }

        preg_match("/{$this->getPlatform()}(.*?);/s", $this->getUserAgent(), $m);
        $this->platform = @$m[0];

    }

    function getPlatform()
    {
        return !empty($this->platform) ? $this->platform : '';
    }

    function getUserAgent()
    {
        return $this->userAgent;
    }

    function getInfoAsString()
    {
        return "{$this->getBrowser()} v{$this->getVersion()}, on {$this->getPlatform()} | {$this->getUserAgent()}";
    }

    function getBrowser()
    {
        return !empty($this->name) ? $this->name : '';
    }

    function getVersion()
    {
        return $this->version;
    }

    function getInfo()
    {
        return (object)[
            'browser' => $this->getBrowser(),
            'version' => $this->getVersion(),
            'user-agent' => $this->getUserAgent(),
            'platform' => $this->getPlatform()
        ];
    }
}