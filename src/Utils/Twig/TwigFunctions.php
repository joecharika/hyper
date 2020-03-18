<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Twig;


use Hyper\Application\HyperApp;
use Hyper\Files\Folder;
use Hyper\Files\ImageHandler;
use Hyper\Functions\Arr;
use Hyper\Functions\Logger;
use Hyper\Functions\Str;
use Hyper\Http\Request;
use Hyper\PWA\ProgressiveWebApp;
use Twig\Environment;
use Twig\TwigFunction;

abstract class TwigFunctions
{
    public static function attach(Environment &$twig)
    {
        foreach (['img', 'optImg', 'base64', 'css', 'js', 'asset', 'get', 'pwa', 'route'] as $fn)
            $twig->addFunction(new TwigFunction($fn, "Hyper\\Twig\\TwigFunctions::$fn"));
    }

    #region Functions
    static function route($action, $controller = null, $routeParams = [], $query = [])
    {
        return ($controller ?? Request::route()->controllerName) . $action . Arr::spread($routeParams,
                false) . Arr::spread($query, true, '&', '=');
    }

    static function img($image, $path = 'img')
    {
        return "/assets/$path/$image";
    }

    static function optImg($image, $size = 10, $path = 'img')
    {
        if (!isset($image)) return null;
        if (Str::contains($image, 'assets'))
            return "$image";

        return ImageHandler::getImage($image, $size);
    }

    static function base64($image, $path = 'img')
    {
        $file = Folder::assets() . "$path/$image";
        $var = base64_encode(file_get_contents($file));
        return "data:;base64,$var";
    }

    static function css($stylesheet, $ext = 'css')
    {
        return self::getAsset($stylesheet, $ext);
    }

    private static function getAsset(string $_, $_t): string
    {
        $file = "{$_t}/{$_}" . (HyperApp::$debug ? ".$_t" : ".min.$_t");


        if (file_exists(Folder::assets() . $file)) {
            return "/assets/$file";
        }

        $file = "{$_t}/{$_}";

        if (file_exists(Folder::assets() . "$file.$_t"))
            $file = "$file.$_t";
        elseif (file_exists(Folder::assets() . "$file.min.$_t"))
            $file = "$file.min.$_t";
        else Logger::log("$_t file, $_ was not found.", Logger::ERROR);

        return "/assets/$file";
    }

    static function js($script, $ext = 'js')
    {
        return self::getAsset($script, $ext);
    }

    static function asset($asset)
    {
        return '/assets/' . $asset;
    }

    #endregion

    #region Utils

    static function get($parent, $key, $match = 'id')
    {
        foreach ($parent as $object) {
            if ($object->$match === $key)
                return $object;
        };

        return null;
    }

    /**
     * PWA Activation script and manifest HTML
     * Activated in production only
     */
    static function pwa()
    {
        if (!HyperApp::$debug) {
            $regJS = (new ProgressiveWebApp())->getRegisterServiceWorkerJS();
            return <<<HTML
            <link rel="manifest" href="/manifest.json">
            <script type="text/javascript">{$regJS}</script>
            HTML;
        } else return '<!-- PWA activation will be injected in production -->';

    }
    #endregion
}