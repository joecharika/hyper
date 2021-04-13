<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application\Routing;


use Hyper\{Application\Http\Request, Application\HyperApp, Application\HyperEventHook, Functions\Str};
use function array_filter;
use function array_values;
use function explode;
use function strtolower;
use function strtr;
use function uniqid;

/**
 * Class Route
 * @package Hyper\Routing
 * @uses \Hyper\Application\HyperApp, \Hyper\Application\Http\Request, \Hyper\Application\HyperEventHook
 */
class Route
{
    public string $id, $name, $path, $controller, $controllerName, $action, $traceablePath;

    /** @var ?string */
    public ?string $module;

    public array $params;

    /**
     * Route constructor.
     * @param string $action
     * @param string $controller
     * @param string $path
     * @param ?string $realController
     * @param ?string $module
     */
    public function __construct(string $action, string $controller, string $path, $realController = null, $module = null)
    {
        $this->id = uniqid();
        $this->path = $path;
        $this->controller = $controller;
        $this->action = Str::toPascal($action, '-');
        $this->module = $module;
        $this->controllerName = $realController ?? strtr(strtolower($controller), [
                'controller' => '',
                '\\' => '',
                'controllers' => ''
            ]);
        $this->name = ucfirst($this->module ?? '') . ucfirst($this->controllerName) . ucfirst($this->action);

        $this->params = [];

//      TODO: Bind posted item to method params
//
//        if (Request::isPost()) {
//            $data = Request::data();
//
//            try {
//                $r = (new ReflectionMethod($controller, $action))->getParameters()[2];
//
//                $data = Request::bind($r->getType(), $data);
//
//            } catch (Exception $e) {
//            }
//
//            $this->params[] = $data;
//        }

        array_push(
            $this->params,
            ...array_values(array_filter($this->isModular() ?
            explode('/',
                strtr($this->path, ["/$this->module" => '', "/$this->controllerName" => '', "/$action" => '']))
            : explode('/', strtr($this->path, ["/$this->controllerName" => '', "/$action" => ''])),
            function ($item) {
                return !empty($item);
            }
        )));

        HyperApp::event(HyperEventHook::routeCreated, $this);
    }

    public function isModular(): bool
    {
        if (strtolower($this->module) === 'index') return false;

        if (empty($this->module)) return false;

        return true;
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

    public function getAppRef(): string
    {
        return $this->isModular() ? $this->module : $this->controllerName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "$this->id: $this->controller/$this->action";
    }
}