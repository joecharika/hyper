<?php

namespace Hyper\Application;

use Hyper\Database\DatabaseConfig;
use Hyper\Exception\{ActionNotFoundException, ControllerNotFoundException, HyperException, HyperHttpException};
use Hyper\Models\User;
use Hyper\Reflection\Annotation;
use Hyper\Routing\{Route, Router};
use Hyper\ViewEngine\Html;
use function array_search;
use function explode;
use function file_exists;
use function method_exists;
use function uniqid;

/**
 * Class HyperApp
 * @package hyper\Application
 */
class HyperApp
{
    /**
     * @var string $name
     * @var array<Route> $routes
     * @var array $sections
     * @var Route $route
     * @var array $imports
     * @var User $user
     * @var bool $debug
     * @var DatabaseConfig $dbConfig
     * @var HyperEventHook $eventHook
     */
    public static
        $name = 'HyperApp',
        $routes,
        $route,
        $imports,
        $user,
        $debug = true,
        $dbConfig,
        $eventHook;

    /**
     * HyperApp constructor.
     * @param string $name The name of your application
     * @param string $routingMode The method of routing used in your application. Default: auto
     * @param bool $usesAuth
     * @param HyperEventHook|null $eventHook
     */
    public function __construct(string $name, string $routingMode = 'auto', $usesAuth = false, HyperEventHook $eventHook = null)
    {
        #initialize the event hook first
        self::$eventHook = $eventHook;

        #Emit HyperEventHook::onBoot event
        $this->emitEvent(HyperEventHook::boot, 'Application is ready to start.');

        #Initialize app data
        HyperApp::$debug = self::config()->debug;
        HyperApp::$dbConfig = new DatabaseConfig();
        HyperApp::$name = $name;
        HyperApp::$routes = Router::create($routingMode);
        if ($usesAuth) HyperApp::$user = (new Authorization())->getSession()->user;
        $this->emitEvent(HyperEventHook::booted, 'Application has been initialised successfully');
        $this->run(HyperApp::$routes);
    }

    /**
     * Trigger an event
     *
     * @param string $event Name od the event
     * @param mixed|null $data Data to pass to the event
     * @return void
     */
    public static function emitEvent(string $event, $data = null): void
    {
        if (isset(self::$eventHook)) self::$eventHook->emit($event, $data);
    }

    /**
     * Get configuration object from specified file
     *
     * @param string $file default => "web.hyper.json"
     * @return object
     */
    public static function config($file = "web.hyper.json"): object
    {
        if (!file_exists($file)) (new HyperException("Configuration file not found", "701"))->throw();
        $text = file_get_contents($file);
        return (object)json_decode($text);
    }

    /**
     * @param array $routes
     */
    private function run(array $routes)
    {

        /** @var Route $route */
        $route = null;

        foreach ($routes as $tempRoute) {
            if (Route::match($tempRoute)) {
                $route = Request::$route = HyperApp::$route = $tempRoute;
                break;
            }
        }

        if (!isset($route))
            $route = Request::$route = HyperApp::$route =
                new Route(
                    Request::params()->action,
                    '\\Controllers\\' . Request::params()->controller . 'Controller',
                    Request::path(),
                    uniqid(),
                );


        $ext = Request::isPost() ? 'post' : 'get';
        $route->action = Request::isPost() ? $ext . $route->action : $route->action;

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . "$route->controller.php")) {

            if (method_exists($route->controller, $ext . HyperApp::$route->action) || method_exists($route->controller,
                    HyperApp::$route->action)) {
                $this->renderAction($route);
            } else
                (new HyperHttpException)->notFound(((new ActionNotFoundException)->message . Html::break() . Html::bold(" ( $route->controller -> $route->action ) ",
                        ['style' => 'color:red'])));
        } else
            (new HyperHttpException)->notFound(((new ControllerNotFoundException)->message . Html::break() . Html::bold(" ( $route->controller ) ",
                    ['style' => 'color:red'])));
    }

    private function renderAction($route)
    {
        #region Init

        $action = $route->action;
        $controller = $route->controller;
        $controllerAllowedRoles = Annotation::getClassAnnotation($controller, 'Authorize');
        $actionAllowedRoles = Annotation::getMethodAnnotation($controller, $action, 'Authorize');

        #endregion

        if (!isset($actionAllowedRoles) && !isset($controllerAllowedRoles)) {
            (new $controller())->$action();
        } else {
            $roles = isset($actionAllowedRoles)
                ? explode('|', $actionAllowedRoles)
                : (!isset($controllerAllowedRoles) ? [] : explode('|', $controllerAllowedRoles));

            if ($this->checkAuth($roles))
                (new $controller)->$action();
            else
                Request::redirectTo('login', 'auth');
        }
    }

    private function checkAuth($roles)
    {
        if (array_search(User::getRole(), $roles) === false) {
            return false;
        }

        return true;
    }
}
