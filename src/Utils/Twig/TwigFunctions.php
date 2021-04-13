<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Utils\Twig;


use Hyper\{Application\Http\Request,
    Application\HyperApp,
    Files\Folder,
    Files\ImageHandler,
    Functions\Arr,
    Functions\Logger,
    Functions\Obj,
    Functions\Str,
    Models\User,
    PWA\ProgressiveWebApp
};
use Twig\TwigFunction;
use function array_map;
use function base64_encode;
use function call_user_func;
use function file_exists;
use function file_get_contents;
use function function_exists;
use function json_encode;
use function shuffle;

abstract class TwigFunctions
{
    public static function getFunctions()
    {
        $functions = [];

        foreach (['img', 'optImg', 'base64', 'css', 'js', 'asset', 'get', 'pwa', 'route', 'json', 'native', 'role', 'stylesheets', 'scripts', 'merge', 'shuffle'] as $fn)
            $functions[] = new TwigFunction($fn, "Hyper\\Utils\\Twig\\TwigFunctions::$fn");

        return $functions;
    }

    #region Functions

    static function merge($obj1, $obj2)
    {
        return Obj::merge($obj1, $obj2);
    }

    static function role(string $role)
    {
        return User::isInRole($role);
    }

    static function route($action, $controller = null, $routeParams = [], $query = [])
    {
        $controller = $controller ?? Request::route()->controllerName;
        $routeParams = (\count($routeParams) > 0 ? '/' : '') . Arr::spread($routeParams, false, '/');
        $query = (\count($query) > 0 ? '?' : '') . Arr::spread($query, true, '&', '=');
        return "/$controller/$action$routeParams$query";
    }

    static function native($function, ...$params)
    {
        if (is_string($function))
            if (function_exists($function))
                return call_user_func($function, ...$params);

        if (is_callable($function))
            return call_user_func($function, ...$params);

        return null;
    }

    static function shuffle($data)
    {
        $keys = array_keys($data);
        shuffle($keys);

        $shuffledArray = [];
        foreach ($keys as $key) $shuffledArray[$key] = $data[$key];

        return $shuffledArray;
    }

    static function json($data)
    {
        return json_encode($data);
    }

    static function img($image, $path = 'img')
    {
        return "/assets/$path/$image";
    }

    static function optImg($image, $size = 30, $path = 'img')
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

    static function asset($asset)
    {
        return '/assets/' . $asset;
    }

    static function stylesheets($styles)
    {
        return array_map(fn($s) => self::css($s), $styles);
    }

    static function css($stylesheet, $folder = 'css', $ext = 'css')
    {
        return self::getAsset($stylesheet, $ext, $folder);
    }

    private static function getAsset(string $_, $_t, $f = null): string
    {
        $f ??= $_t;
        $file = "{$f}/{$_}" . (HyperApp::$debug ? ".$_t" : ".min.$_t");


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

    static function scripts($s)
    {
        return array_map(fn($js) => self::js($js), $s);
    }

    static function js($script, $ext = 'js')
    {
        return self::getAsset($script, $ext);
    }

    #endregion

    #region Utils

    static function get($parent, $key, $match = 'id')
    {
        foreach ($parent as $object) {
            if ($object->$match === $key)
                return $object;
        }

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