<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Routing;


use Hyper\Application\HyperApp;
use Hyper\Application\HyperEventHook;
use Hyper\Http\Request;

/**
 * Class Route
 * @package Hyper\Routing
 * @uses \Hyper\Application\HyperApp, \Hyper\Http\Request, \Hyper\Application\HyperEventHook
 */
class Route
{
    /**
     * @var
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $path;
    /**
     * @var string
     */
    public $controller;

    /** @var string */
    public $controllerName;

    /** @var string */
    public $module;

    /**
     * @var string
     */
    public $action;

    /**
     * Route constructor.
     * @param string $action
     * @param string $controller
     * @param string $path
     * @param string $name
     * @param string|null $realController
     */
    public function __construct(string $action, string $controller, string $path, string $name, string $realController = null, $module = null)
    {
        $this->id = uniqid();
        $this->name = $name;
        $this->path = $path;
        $this->controller = $controller;
        $this->action = $action;
        $this->module = $module;

        $names = explode('\\', $controller);
        $this->controllerName= $realController ?? strtr($names[array_key_last($names)], ['Controller' => '']);

        HyperApp::event(HyperEventHook::routeCreated, $this);
    }

    /**
     * Checks if given route matches the currently visited route
     * @param $route
     * @return bool
     */
    public static function match(Route $route): bool
    {
        return (Request::route()->controller === $route->controller) && (Request::route()->action === $route->action);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "$this->id: $this->controller/$this->action";
    }
}