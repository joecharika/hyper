<?php


namespace Hyper\Application;


use Hyper\Exception\HyperHttpException;
use Hyper\Exception\NullValueException;
use Hyper\Functions\{Arr, Obj, Str};
use Hyper\Notifications\HttpMessage;
use Hyper\Reflection\Annotation;
use Hyper\Routing\Route;
use function explode;
use function header;
use function property_exists;
use function strtolower;

/**
 * Class Request
 * @package Hyper\Application
 */
class Request
{
    /** @var Route */
    public static $route;
    /** @var HttpMessage */
    public static $message;

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
        return strtolower(explode('/', $_SERVER['SERVER_PROTOCOL'])[0]);
    }

    /**
     * @return string
     */
    public static function server()
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * @return string
     */
    public static function host()
    {
        return $_SERVER['localhost'];
    }

    /**
     * @return string
     */
    public static function port()
    {
        return $_SERVER['SERVER_PORT'];
    }

    public static function path()
    {
        return Arr::key($_SERVER, 'PATH_INFO', '/');
    }

    public static function previousUrl()
    {
        return Arr::key($_SERVER, 'HTTP_REFERER', '/');
    }

    /**
     * @param null $message
     * @param string $type
     * @return array
     */
    public static function notification($message = null, $type = null)
    {
        return [
            'hasMessage' => isset($message) ? true : Request::hasMessage(),
            'message' => $message ?? Request::$message ?? Request::message(),
            'messageType' => $type ?? Obj::property(Request::$message, 'type', 'info')
        ];
    }

    /**
     * @return bool
     */
    public static function hasMessage()
    {
        return isset(Request::$message) ? true : array_key_exists('message', $_GET);
    }

    /**
     * @return string
     */
    public static function message()
    {
        return Obj::property(Request::get(), 'message', Request::$message ?? '');
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
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return bool
     */
    public static function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == "GET";
    }

    /**
     * Checks if given route matches the currently visited route
     * @param $route
     * @return bool
     */
    public static function matchUrl(string $route)
    {
        $params = explode("/", strtolower("$route"));

        return strtolower(Request::params()->controller) == Arr::safeArrayGet($params, 1, "home")
            && strtolower(Request::params()->action) == Arr::safeArrayGet($params, 2, "index");
    }

    /**
     * @return object
     */
    public static function params()
    {
        $params = explode("/", Arr::safeArrayGet($_SERVER, 'PATH_INFO', null));

        $action = Arr::safeArrayGet($params, 2, 'Index');
        $action = strlen($action) === 0 ? 'Index' : $action;

        $id = Arr::safeArrayGet($params, 3, null);
        $id = strlen($id) === 0 ? null : $id;

        return (object)[
            "controller" => ucfirst(Arr::safeArrayGet($params, 1, "Home")),
            "action" => ucfirst(Str::toPascal($action, "-")),
            "id" => $id,
            "query" => Request::query(),
            "route" => Request::$route,
        ];
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
     * @param object $object The object to bind
     * @return object The same object with values from the request
     */
    public static function bind(object $object): object
    {

        $class = strtolower(get_class($object));
        $properties = get_class_vars($class);

        $object = (array)$object;

        foreach ($properties as $property => $value) {
            if (property_exists(Request::data(), $property)) {
                if (Annotation::getPropertyAnnotation($class, $property, 'isFile')) {
                    $hasFile = !empty(Obj::property(Request::files(), $property)['tpm_name']);
                    $object[$property] = $hasFile ? Request::files()->$property : Request::data()->$property;
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
        return $_SERVER['REQUEST_METHOD'] == "POST";
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
     * @param HttpMessage $message
     * @param array $query
     */
    public static function redirectTo($action, $controller, $param = null, HttpMessage $message = null, array $query = [])
    {
        $message = !isset($message) ? '' : "?message=$message";
        $param = !isset($param) ? '' : $param;
        $action = $action === 'index' ? '' : "$action/";
        header("Location: /$controller/$action$param$message");
    }

    /**
     * Redirect to a given url with a message query
     *
     * @param $url
     * @param HttpMessage $message
     * @param array $query
     */
    public static function redirectToUrl($url, HttpMessage $message = null, $query = [])
    {
        $message = isset($message) ? "?message=$message" : '';

        if (isset($url))
            header("Location: $url$message");
        else (new NullValueException)->throw();
    }

    /**
     * Get a model from the submitted id parameter (/{controller}/{action}/{id})
     *
     * @param array $with
     * @return object|null
     */
    public static function fromParam($with = [])
    {
        $model = null;
        $class = new Request::$route->controller;

        if (!is_null(Request::params()->id)) {
            $model = $class->db->firstById(Request::params()->id, $with);
            if (is_null($model)) (new HyperHttpException)->notFound();
        } else (new HyperHttpException)->badRequest();

        return $model;
    }
}