<?php


namespace Hyper\Routing;


use Hyper\Application\Request;

class Route
{
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
    }

    /**
     * Checks if given route matches the currently visited route
     * @param $route
     * @return bool
     */
    public static function match($route)
    {
        return Request::params()->controller . 'Controller' == $route->controller && Request::params()->action == $route->action;
    }

    public function __toString()
    {
        return $this->name;
    }
}