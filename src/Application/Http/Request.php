<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application\Http;

use Hyper\{Application\Annotations\file,
    Application\HyperApp,
    Application\Routing\Route,
    Exception\HyperError,
    Exception\HyperHttpException,
    Exception\NullValueException,
    Functions\Arr,
    Functions\Obj,
    Functions\Str,
    SQL\Database\DatabaseContext,
    SQL\SqlOperator};
use Helpers\Utils\Cryptography;
use function apache_request_headers;
use function array_merge;
use function count;
use function explode;
use function header;
use function is_string;
use function json_decode;
use function preg_match;
use function preg_replace;
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
    /** @var Route */
    public static Route $route;
    public $requestUri,
        $previousUrl,
        $queryString,
        $requestMethod,
        $path,
        $fullPath,
        $headers;
    public object $post, $files, $data, $query;
    public string $port, $host, $server, $protocol, $url, $localUrl, $serverUrl;
    public Route $currentRoute;
    public array $params;
    #endregion

    #region Static

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->url = Request::url();
        $this->localUrl = Request::localUrl();
        $this->params = Request::params();
        $this->currentRoute = Request::route();
        $this->protocol = Request::protocol();
        $this->server = Request::server();
        $this->host = Request::host();
        $this->port = Request::port();
        $this->serverUrl = "{$this->protocol}://{$this->host}";
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
        $this->headers = Request::headers();
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
    public static function localUrl()
    {
        return Request::path() . Arr::key(explode('?', Arr::key($_SERVER, 'REQUEST_URI', '/')), 1);
    }

    public static function path()
    {
        return Arr::key(explode('?', Arr::key($_SERVER, 'REQUEST_URI', '/')), 0);
    }

    /**
     * Get HttpRequest parameters
     * {
     *      id: first_param_after_action,
     *      param0: params_thereafter,
     *      param1: ...
     * }
     * @param ?Route $route
     * @return array
     */
    public static function params(Route $route = null): array
    {
        $route = $route ?? Request::route();

        $params = [];

        if (count($route->params) <= 0) return $params;

        foreach ($route->params as $key => $param) {
            $params["param$key"] = $param;
        }

        return $params;
    }

    /**
     * @return Route
     */
    public static function route(): Route
    {
        $path = Request::path();
        $pathAsArray = explode('/', $path);
        $app = @HyperApp::instance();

        $modules = isset($app) ? $app->getModules() : [];
        $moduleName = $pathAsArray[1];

        $moduleName = isset($modules->$moduleName)
            ? $moduleName : null;

        $index = isset($modules->$moduleName)
            ? 1 : 0;

        $action = Arr::key($pathAsArray, $index + 2, 'Index');
        $action = empty($action) ? 'Index' : $action;

        $controller = Arr::key($pathAsArray, $index + 1, 'Home');
        $controller = empty($controller) ? 'Home' : $controller;

        return new Route(
            $action,
            (@$modules->$moduleName ?? '\\Controllers\\') . ucfirst(Str::toPascal($controller, '-')) . 'Controller',
            $path,
            $controller,
            $moduleName
        );
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
     * @return object
     */
    public static function query()
    {
        $array_merge = array_key_exists('page', $_GET) ? $_GET : array_merge($_GET, ['page' => 1]);
        return (object)$array_merge;
    }

    public static function data()
    {
        if (Request::isGet()) return Request::get();

        if (!empty((array)Request::post())) return Request::post();

        return (object)json_decode(file_get_contents('php://input', 'r'), true);
    }

    /**
     * @return bool
     */
    public static function isGet()
    {
        return @$_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * @return object
     */
    public static function get()
    {
        return (object)$_GET;
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
     * @return mixed
     */
    public static function method()
    {
        return strtolower(@$_SERVER['REQUEST_METHOD'] ?? 'unknown');
    }

    public static function headers()
    {
        if (!function_exists('apache_request_headers')) {
            $arh = [];
            $rx_http = '/\AHTTP_/';
            foreach ($_SERVER as $key => $val)
                if (preg_match($rx_http, $key)) {
                    $arh_key = preg_replace($rx_http, '', $key);
                    $rx_matches = explode('_', $arh_key);

                    if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                        foreach ($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                        $arh_key = implode('-', $rx_matches);
                    }

                    $arh[$arh_key] = $val;
                }

            return ($arh);
        }

        return apache_request_headers();
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

    /**
     * Binds given object to Request post/get params
     *
     * @param object|null $object The object to bind
     * @param Request|null $request
     * @return object The same object with values from the request
     */
    public static function bind($object, ?Request $request = null): object
    {
        if (!isset($object)) return (object)[];

        $class = strtolower(get_class($object));
        $properties = get_class_vars($class);
        $request = $request ?? new Request();

        $object = (array)$object;

        foreach ($properties as $property => $value) {
            if (property_exists($request->data, $property) || property_exists($request->files, $property)) {
                if (file::of($class, $property)) {
                    $hasFile = !empty(Obj::property($request->files, $property)['tmp_name']);
                    $object[$property] = $hasFile ? @$request->files->$property : @$request->data->$property;
                } else $object[$property] = empty($request->data->$property) ? null : $request->data->$property;
            }
        }

        return (object)$object;
    }

    /**
     * @return bool
     */
    public static function isPost()
    {
        return @$_SERVER['REQUEST_METHOD'] == "POST";
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

        $message->message = Cryptography::encrypt($message->message);
        $message->messageType = Cryptography::encrypt($message->messageType);

        $message = isset($message) ? (isset($message->message) ? "?$message" : '') : '';

        $query = Arr::spread(\array_map(fn($q) => Cryptography::encrypt($q) ,(array)$query), true, '&', '=');

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
     * @param ?HttpMessage|string $message
     * @return string
     * @deprecated Use redirect
     */
    public static function redirectToUrl($url, $message = null)
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
     * @param null $model
     * @param null $parents
     * @param null $lists
     * @return object|null|void
     */
    public static function fromParam($model = null, $parents = null, $lists = null)
    {
        if (!is_null(@Request::params()['param0']) or !is_null(@Request::data()->id)) {
            $item = DatabaseContext
                ::of(Str::singular($model ?? Request::$route->controllerName))
                ->first('id', Request::params()['param0'] ?? Request::data()->id, SqlOperator::equal, $parents, $lists);

            if (!isset($item)) self::error(HyperHttpException::notFound());

            return $item;
        } else self::error(HyperHttpException::badRequest());
    }

    public function addQuery(string $url, array $query)
    {
        $queryPart = explode('?', $url);

        $queryNew = [];

        if (count($queryPart) >= 2) {
            $url = $queryPart[0];
            foreach (explode('&', $queryPart[1]) as $i) {
                $i = explode('=', $i);
                $queryNew[$i[0]] = $i[1];
            }
        }

        $queryPart = array_merge($queryNew, $query);

        foreach ($queryPart as $key => $value) {
            if (isset($value))
                $url .= (Str::contains($url, '?') ? '&' : '?') . "$key=$value";
        }

        return $url;
    }

    /**
     * @param string $head Formats ['controller.action.{param1.param2...}{?message}', 'controller/action/{param1/param2...}{?message}']
     * @param ?string|HttpMessage $message Leave blank if message was included in $head. Message in head takes preference
     * @param array $query Additional query parameters
     * @return string
     */
    public function redirect($head, $message = null, array $query = [])
    {
        if (Str::contains($head, '?')) {
            $head = explode('?', $head);
            $head = @$head[0];
        }

        if (!Str::startsWith($head, 'http'))
            if (Str::contains($head, '.'))
                $head = '/' . strtr($head, ['.' => '/']);

        if (isset($message)) {
            if (!$message instanceof HttpMessage)
                $message = new HttpMessage($message);

            $message->message = Cryptography::encrypt($message->message);
            $message->messageType = Cryptography::encrypt($message->messageType);

            $message = "?$message";
        }

        if (isset($query)) {
            foreach ($query as $key => $value) {
                $s = Cryptography::encrypt($value);
                $message .= $message . (empty($message) ? '?' : '&') . "$key=$s";
            }
        }

        $url = "$head$message";

        if (Str::startsWith($url, 'http') || Str::startsWith($url, '/'))
            header("Location: $url");
        else
            header("Location: /$url");

        return "Redirecting to $url...";
    }

    #endregion
    public function urlPath(string $path)
    {
        $string = Str::startsWith($path, '/') ? $path : "/$path";
        return "{$this->serverUrl}{$string}";
    }
}
