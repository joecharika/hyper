<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Http;

use Hyper\Application\HyperApp;
use Hyper\Exception\{HyperError, HyperHttpException, NullValueException};
use Hyper\Functions\{Arr, Debug, Obj, Str};
use Hyper\Reflection\Annotation;
use Hyper\Routing\Route;
use Hyper\SQL\SqlOperator;
use function array_slice;
use function count;
use function explode;
use function header;
use function Hyper\Database\db;
use function is_string;
use function property_exists;
use function strlen;
use function strtolower;

/**
 * Class Request
 * @package Hyper\Http
 */
class Request
{
    use HyperError;

    #region Instance
    public
        $url,
        $params,
        $currentRoute,
        $protocol,
        $server,
        $host,
        $port,
        $requestUri,
        $previousUrl,
        $query,
        $queryString,
        $data,
        $files,
        $post,
        $requestMethod,
        $path,
        $fullPath;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->url = Request::url();
        $this->params = Request::params();
        $this->currentRoute = Request::route();
        $this->protocol = Request::protocol();
        $this->server = Request::server();
        $this->host = Request::host();
        $this->port = Request::port();
        $this->requestUri = Request::requestUri();
        $this->previousUrl = Request::previousUrl();
        $this->query = Request::query();
        $this->queryString = Arr::key(explode('?', Arr::key($_SERVER, 'REQUEST_URI', '/')), 1);
        $this->data = Request::data();
        $this->files = Request::files();
        $this->post = Request::post();
        $this->requestMethod = Request::method();
        $this->path = Request::path();
        $this->fullPath = Arr::key($_SERVER, 'REQUEST_URI', '/');
    }
    #endregion

    #region Static

    /** @var Route */
    public static $route;

    /**
     * @param string $head Formats ['controller.action.{param1.param2...}{?message}', 'controller/action/{param1/param2...}{?message}']
     * @param string|HttpMessage $message Leave blank if message was included in $head. Message in head takes preference
     * @param array $query Additional query parameters
     * @return string
     * @throws HyperHttpException
     */
    public function redirect($head, $message = null, array $query = [])
    {
        if (Str::contains($head, '?')) {
            $head = \explode('?', $head);

            if(Str::contains($head[\array_key_last($head)] , 'message') && isset($message))
                throw new HyperHttpException('Message definition is ambiguous. Redirect head contains message parameter, and the method message parameter is also defined');

            $message = $head[\array_key_last($head)];

            array_pop($head);
            $head = @$head[0];
        }

        if (Str::contains($head, '.'))
            $head = '/' . strtr($head, ['.' => '/']);


        if (isset($message)) {
            if (!$message instanceof HttpMessage)
                $message = new HttpMessage($message);
            $message = "?$message";
        }


        if (isset($query)) {
            foreach ($query as $key => $value) {
                $message .= $message . (empty($message) ? '?' : '&') . "$key=$value";
            }
        }

        $url = "$head$message";

        if (parse_url($url, PHP_URL_SCHEME) !== '')
            header("Location: $url");
        else
            header("Location: /$url");

        return "Redirecting to $url...";
    }


    /**
     * @return string
     */
    public static function url()
    {
        return Request::protocol() . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
    public static function protocol()
    {
        return strtolower(@explode('/', $_SERVER['SERVER_PROTOCOL'])[0]);
    }

    /**
     * @return string
     */
    public static function server()
    {
        return @$_SERVER['SERVER_NAME'];
    }

    /**
     * @return string
     */
    public static function host()
    {
        return @$_SERVER['HTTP_HOST'];
    }

    /**
     * @return string
     */
    public static function port()
    {
        return @$_SERVER['SERVER_PORT'];
    }

    public static function requestUri()
    {
        return Arr::key($_SERVER, 'REQUEST_URI', '/');
    }

    public static function previousUrl()
    {
        return Arr::key($_SERVER, 'HTTP_REFERER', '/');
    }

    /**
     * @return bool
     */
    public static function hasMessage()
    {
        return array_key_exists('message', $_GET);
    }

    /**
     * @return HttpMessage
     */
    public static function message(): HttpMessage
    {
        return new HttpMessage(
            Obj::property(Request::get(), 'message', ''), Obj::property(Request::get(), 'messageType', ''),
            Obj::property(Request::get(), 'action', '')
        );
    }

    /**
     * @return object
     */
    public static function get()
    {
        return (object)$_GET;
    }

    /**
     * @return mixed
     */
    public static function method()
    {
        return @$_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return bool
     */
    public static function isGet()
    {
        return @$_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Checks if given route matches the currently visited route
     * @param $route
     * @return bool
     */
    public static function matchUrl(string $route)
    {
        $params = explode("/", strtolower("$route"));

        return strtolower(Request::route()->controller) === Arr::key($params, 1, 'home')
            && strtolower(Request::route()->action) === Arr::key($params, 2, 'index');
    }

    public static function isModular(){
        $path = Request::path();
        $pathAsArray = explode('/', $path);

        $modules = HyperApp::instance()->config->modules;
        $moduleName = $pathAsArray[1];

        return isset($modules->$moduleName);
    }

    /**
     * @return Route
     */
    public static function route(): Route
    {
        $path = Request::path();
        $pathAsArray = explode('/', $path);
        $moduleName = $pathAsArray[1];

        $index = Request::isModular()
            ? 1 : 0;

        $action = Arr::key($pathAsArray, $index + 2, 'Index');
        $action = empty($action) ? 'Index' : $action;

        $controller = Arr::key($pathAsArray, $index + 1, 'Home');
        $controller = empty($controller) ? 'Home' : $controller;

        return new Route(
            ucfirst(Str::toPascal($action, '-')),
            (@HyperApp::instance()->config->modules->$moduleName ?? '\\Controllers\\') . ucfirst(Str::toPascal($controller, '-')) . 'Controller',
            $path,
            uniqid(),
            $controller,
            $moduleName
        );
    }

    public static function path()
    {
        return Arr::key(explode('?', Arr::key($_SERVER, 'REQUEST_URI', '/')), 0);
    }

    /**
     * @return object
     */
    public static function query()
    {
        $array_merge = array_key_exists('page', $_GET) ? $_GET : array_merge($_GET, ['page' => 1]);
        return (object)$array_merge;
    }

    /**
     * Binds given object to Request post/get params
     *
     * @param object|null $object The object to bind
     * @return object The same object with values from the request
     */
    public static function bind($object): object
    {
        if (!isset($object)) return (object)[];

        $class = strtolower(get_class($object));
        $properties = get_class_vars($class);

        $object = (array)$object;

        foreach ($properties as $property => $value) {
            if (property_exists(Request::data(), $property) || property_exists(Request::files(), $property)) {
                if (Annotation::getPropertyAnnotation($class, $property, 'file')) {
                    $hasFile = !empty(Obj::property(Request::files(), $property)['tmp_name']);
                    $object[$property] = $hasFile ? @Request::files()->$property : @Request::data()->$property;
                } else $object[$property] = Request::data()->$property;
            }
        }

        return (object)$object;
    }

    public static function data()
    {
        return Request::isPost() ? Request::post() : Request::get();
    }

    /**
     * @return bool
     */
    public static function isPost()
    {
        return @$_SERVER['REQUEST_METHOD'] == "POST";
    }

    /**
     * @return object
     */
    public static function post()
    {
        return (object)$_POST;
    }

    /**
     * @return object
     */
    public static function files()
    {
        return (object)$_FILES;
    }

    /**
     * Redirect to a controller action within the application
     *
     * @param $action
     * @param $controller
     * @param string|null $param
     * @param HttpMessage|string|null $message
     * @param array $query
     * @return string
     */
    public static function redirectTo($action, $controller, $param = null, $message = null, $query = [])
    {
        $message = is_string($message) ? new HttpMessage($message) : $message;

        $message = isset($message) ? (isset($message->message) ? "?$message" : '') : '';

        $query = Arr::spread((array)$query, true, '&', '=');

        $query = empty($message) ? (empty($query) ? '' : "?$query") : $query;

        $param = !isset($param) ? '' : $param;
        $action = $action === 'index' ? '' : "$action/";

        header("Location: /$controller/$action$param$message$query");

        return 'Redirecting...';
    }

    /**
     * Redirect to a given url with a message query
     *
     * @param $url
     * @param HttpMessage|string $message
     * @param array $query
     * @return string
     * @deprecated Use redirect
     */
    public static function redirectToUrl($url, $message = null, $query = [])
    {
        $message = is_string($message) ? new HttpMessage($message) : $message;

        $message = isset($message) ? (Str::contains($url, '?') ? '&' : '?') . "$message" : '';

        if (isset($url))
            header("Location: $url$message");
        else self::error(new NullValueException);

        return "Redirecting to $url";
    }

    /**
     * Get a model from the submitted id parameter (/{controller}/{action}/{id})
     * @param null $parents
     * @param null $lists
     * @return object|null
     */
    public static function fromParam($parents = null, $lists = null)
    {
        $model = null;


        if (!is_null(@Request::params()->id) or !is_null(@Request::data()->id)) {
            $model = db(Str::singular(Request::$route->controllerName))
                ->first('id', Request::params()->id ?? Request::data()->id, SqlOperator::equal, $parents, $lists);

            if (!isset($model)) self::error(HyperHttpException::notFound());
        } else self::error(HyperHttpException::badRequest());

        return $model;
    }

    /**
     * Get HttpRequest parameters
     * {
     *      id: first_param_after_action,
     *      param0: params_thereafter,
     *      param1: ...
     * }
     * @return object
     */
    public static function params(): object
    {
        $params = explode('/', self::path());

        $index = Request::isModular() ? 1 : 0;

        $id = Arr::key($params, $index + 3, null);
        $id = strlen($id) === 0 ? null : $id;

        $obj = ['id' => $id];

        if (count($params) > ($index + 3)) {
            foreach (array_slice((array)$params, $index + 4) as $key => $value) {
                $obj["param$key"] = $value;
            }
        }

        return (object)$obj;
    }

    #endregion
}
