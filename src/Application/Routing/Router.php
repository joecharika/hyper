<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Routing;

use Hyper\Application\HyperApp;
use Hyper\Application\HyperEventHook;
use function array_key_last;
use function array_merge;
use function array_push;
use function array_unique;
use function explode;
use function is_dir;
use function scandir;
use function strtolower;

class Router
{
    /**
     * Creates the routing system
     *
     * @param string $routingMode Type of routing manual|auto|mixed default is auto
     * @param array $routes
     * @return array
     */
    public static function create(string $routingMode = 'auto', $routes = []): array
    {
        #Emit HyperEventHook::onRoutingStarting event
        HyperApp::event(HyperEventHook::routingStarting);

        #region Init
        $autoRoutes = [];
        #endregion

        #region Routing
        if ($routingMode === RoutingMode::AUTO || $routingMode === RoutingMode::MIXED) {
            $folders = scandir('views');
            foreach ($folders as $key_1 => $folder) {
                if ($folder !== '.' && $folder !== '..' && $folder !== 'shared') {
                    if (is_dir("views/$folder")) {
                        $views = scandir("views/$folder");
                        foreach ($views as $key_2 => $view) {
                            if ($view !== '.' && $view !== '..' && $folder != 'shared' && $folder != 'includes' && !is_dir($view)) {

                                $r = explode('.', $view);

                                if ($r[array_key_last($r)] === 'twig') {

                                    $controller = $folder;
                                    $action = $r[0];

                                    $controller_param = '/';
                                    $view_param = '';
                                    if (strtolower($controller) !== 'home') {
                                        $controller_param .= "$folder/";
                                    }
                                    if (strtolower($action) != 'index') {
                                        $view_param = $r[0];
                                        if (strtolower($controller) === 'home') {
                                            $controller_param = '/';
                                        }
                                    }
                                    array_push(
                                        $autoRoutes,
                                        new Route(
                                            ucfirst($action),
                                            '\\Controllers\\' . ucfirst($controller) . 'Controller',
                                            "$controller_param$view_param",
                                            ucfirst($controller) . ucfirst($action)
                                        )
                                    );
                                }
                            }
                        }
                    }
                }
            }
            if ($routingMode === RoutingMode::AUTO) return array_unique($autoRoutes);
        }

        if ($routingMode === RoutingMode::MIXED) {
            $routes = array_merge($autoRoutes, $routes);
        }
        #endregion

        #Emit HyperEventHook::onRoutingCompleted event
        HyperApp::event(HyperEventHook::routingCompleted);

        return array_unique($routes);
    }

    /**
     * Create a new Router::class object
     *
     * @param string $path
     * @param string $controller
     * @param string $action
     * @return Route
     */
    public static function route(string $path, string $controller = 'home', string $action = 'index'): Route
    {
        return new Route(
            ucfirst($action),
            '\\Controllers\\' . ucfirst($controller) . 'Controller',
            $path,
            ucfirst($controller) . ucfirst($action)
        );
    }
}
