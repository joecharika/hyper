<?php


namespace Hyper\Routing;


use Hyper\Application\HyperApp;
use Hyper\Application\HyperEventHook;
use Hyper\Application\Request;

class Route
{
    public $id;
    public $name;
    public $path;
    public $controller;
    public $action;

    public function __construct(string $action, string $controller, string $path, string $name)
    {
        $this->name = $name;
        $this->path = $path;
        $this->controller = $controller;
        $this->action = $action;

        HyperApp::emitEvent(HyperEventHook::routeCreated, $this);
    }

    /**
     * Checks if given route matches the currently visited route
     * @param $route
     * @return bool
     */
    public static function match(Route $route)
    {
        return Request::path() === $route->path;
    }

    public function __toString()
    {
        return $this->path;
    }
}