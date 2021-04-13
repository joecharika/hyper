<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application;

use Exception;
use Hyper\Application\Annotations\action;
use Hyper\Application\Annotations\authorize;
use Hyper\Application\api\HyperApiApplication;
use Hyper\Application\Http\Cookie;
use Hyper\Application\Http\Request;
use Hyper\Application\Http\StatusCode;
use Hyper\Functions\Debug;
use Hyper\Application\Routing\{Route};
use Hyper\Exception\{HyperError, HyperException, HyperHttpException};
use Hyper\Functions\Logger;
use Hyper\Functions\Str;
use Hyper\Models\User;
use Hyper\PWA\ProgressiveWebApp;
use Hyper\SQL\Database\DatabaseConfig;
use Hyper\Utils\General;
use function array_filter;
use function array_merge;
use function explode;
use function file_exists;
use function is_null;
use function is_string;
use function method_exists;
use function ucfirst;

/**
 * Class HyperApp
 * @package hyper\Application
 */
class HyperApp
{
    use HyperError;

    #TODO: HyperWeb, HyperOAuth, HyperBot;

    #region Properties
    public static
        /**
         * Signed-in user
         * @var User
         */
    ?User $user;

    public static
        /**
         * Current debug state
         * @var bool
         */
        $debug = true,
        /**
         * Database configuration object
         * @var DatabaseConfig
         */
        $dbConfig,
        /**
         * Temporary static app storage,
         * @var array
         */
        $storage = [];

    protected static ?HyperApp $instance;

    public string $name = 'HyperApp';

    public object $config;

    public array $routes, $apps;

    public ?HyperEventHook $eventHook;

    #endregion

    #region Init

    /**
     * HyperApp constructor.
     * @param string $name The name of your application
     * @param bool $usesAuth
     * @param HyperEventHook|null $eventsHook
     */
    public function __construct(string $name, $usesAuth = false, HyperEventHook $eventsHook = null, array $apps = [])
    {
        # Set the application instance for global access
        self::$instance = $this;

        # initialize the event hook first
        $this->eventHook = $eventsHook;

        # Emit HyperEventHook::onBoot event => booting starting
        $this->event(
            HyperEventHook::boot,
            'Application is ready to start'
        );

        # Initialize app data
        $this->config = self::config();
        $this->apps = $apps;
        HyperApp::$debug = $this->config->debug;
        HyperApp::$dbConfig = new DatabaseConfig();
        $this->name = $name ?? $this->name;

        # Activate app parks
        $this->gzip();
        $this->ddos();

        # Clear last request queries
        Logger::log('', '__INIT__', 'last_query', 'w');

        # Emit HyperEventHook::onBooted event => booting completed
        $this->event(
            HyperEventHook::booted,
            'Application has been initialised successfully'
        );

        # Run application
        try {
            self::$user = null;
            $this->run($usesAuth);
        } catch (Exception $e) {
            self::error($e);
        }

        $this->event(
            HyperEventHook::exiting,
            'Application has completed handling the request'
        );
    }

    /**
     * Prepare output for g-zip compression
     */
    protected function gzip()
    {
        if(!$this->config->debug){
            if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
                ob_start();
            } elseif (strpos(' ' . $_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') == false) {
                if (strpos(' ' . $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') == false) {
                    ob_start();
                } elseif (!ob_start("ob_gzhandler")) {
                    ob_start();
                }
            } elseif (!ob_start("ob_gzhandler")) {
                ob_start();
            }
        }
    }

    /**
     * Get configuration object from specified file
     * @param string $file default => 'hyper.config.json'
     * @return HyperConfig|object
     */
    public static function config($file = 'hyper.config.json')
    {
        # If config file was not found return default config
        if (!file_exists($file) || !isset($file))
            return new HyperConfig;

        # Else return the config from config file
        return json_decode(file_get_contents($file));
    }

    /**
     * Run app level DDoS protection
     */
    protected function ddos()
    {
        $config = self::config();
        $ipAddress = General::ipAddress();

        if ($config->limitRequests && $ipAddress) {
            $cookie = new Cookie;

            $ddosKey = '__hyper-piXhjs984Mhfo::f8Hdksm';
            $ddosKeyPair = $cookie->getCookie($ddosKey);

            if (Str::endsWith($ddosKeyPair, '.020')) {
                $cookie->removeCookie($ddosKey);
                header('refresh:7;url=' . Request::url(), false, StatusCode::TOO_MANY_REQUESTS);
                self::error(new HyperException(
                    'Your consistence is amazing, but lets take a break... . Refreshing...',
                    StatusCode::TOO_MANY_REQUESTS
                ));
            } else {
                $cookie->addCookie(
                    $ddosKey,
                    hash('gost-crypto', $ipAddress) . '.0' . ((int)substr($ddosKeyPair, -2) + 1),
                    time() + 20,
                    '/'
                );
            }
        }
    }
    #endregion

    /**
     * Trigger an event
     * @param string $event Name od the event
     * @param mixed|null $data Data to pass to the event
     * @return void
     */
    public static function event(string $event, $data = null): void
    {
        $instance = HyperApp::instance();

        if (is_null($instance)) return;

        if (!isset($instance->eventHook))
            $instance->eventHook = new HyperEventHook([]);

        $instance->eventHook->emit($event, $data);
    }

    /**
     * Get application instance
     * @return ?HyperApp
     */
    public static function instance(): ?HyperApp
    {
        return isset(self::$instance) ? self::$instance : null;
    }

    /**
     * @param $usesAuth
     * @throws Exception
     */
    protected function run(bool $usesAuth)
    {
        $route = Request::$route = Request::route();
        $ref = strtolower($route->getAppRef());

        switch ($ref) {
            case 'api':
                $this->api($route);
                break;
            case 'oauth':
                $this->oauth($route);
                break;
            case 'bot':
                $this->bot($route);
                break;
            case 'pwa':
                $this->pwa($route);
                break;
            case 'config':
                $this->configUI();
                break;
            default:

                $customApp = \array_key_exists($ref, $this->apps);

                if (!$customApp) {
                    HyperApp::$user = $usesAuth
                        ? (new Authorization())->getSession()->user
                        : new User;

                    $this->web($route);
                } else {
                    call_user_func($this->apps[$ref], $route);
                }
        }
    }

    protected function api(Route $route)
    {
        new HyperApiApplication($route);
    }

    protected function oauth(Route $route)
    {
        self::error('OAuth is not available');
    }

    #region Application Types

    protected function bot(Route $route)
    {
        self::error('Bot is not available');
    }

    /**
     * @param Route $route
     * @return ProgressiveWebApp
     * @throws Exception when visited in production
     */
    protected function pwa(Route $route): ProgressiveWebApp
    {
        if ($this->config->debug) {

            Debug::dump(new ProgressiveWebApp($this->name));


            $pwa = new ProgressiveWebApp($this->name);



            $result = null;


            switch (strtolower($route->action)) {
                case 'install':
                case 'save':
                    $result = $pwa->save();
                    break;
                case 'manifest':
                    $result = nl2br($pwa->getManifest());
                    break;
                case 'precache-manifest':
                case 'precachemanifest':
                case 'precache':
                    $result = nl2br($pwa->getPreCacheManifest());
                    break;
                case 'service-worker':
                case 'serviceworker':
                case 'sw':
                    $result = nl2br($pwa->getServiceWorker());
                    break;
                case 'register-js':
                case 'registerJS':
                case 'js':
                    $result = nl2br($pwa->getRegisterServiceWorkerJS());
                    break;
                case 'index':
                default:
                    $result = <<<HTML
                        <style>
                            body{margin:0;padding:0;font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";background-color: #fefefe}
                            h4{position: static; display: block; padding: 1em 3em;background-color: #fff;box-shadow: 0 0 10px #777777}
                            article{display: block; padding: .5em 3em;}
                            small{color: #777777}
                            a{background-color: rebeccapurple;display: inline-block; padding: 4px 16px;color: #ffffff;border-radius: 36px;text-decoration: none;margin: .12em}
                            ul{list-style-type: none}
                            li{margin: 1em 0}
                            p{display: inline-block}
                            h6{font-size: 18px;font-weight: lighter;margin: .2em 0}
                            h6:before{content: "~"; margin: 0 .2em}
                            h5{font-size: 20px;font-weight: lighter;margin: .2em 0}
                        </style>
                        <h4>Hyper PWA Setup</h4>
                        <article>
                            <h5>Welcome to Hyper Progressive Web App Setup</h5>
                            <small>Here is the list of available routes</small><br>
                            <small><strong>All routes defined here must start with /pwa/{route}</strong></small>
                            <ul>
                                <li>
                                    <h6>Installation</h6>
                                    <small><a href="/pwa/save">/save</a> -- or -- <a href="/pwa/install">/install</a></small>
                                    <p>Create the necessary pwa files, what is left for you is to register the service worker</p>
                                </li>
                                <li>
                                    <h6>Manifest</h6>
                                    <small><a href="/pwa/manifest">/manifest</a></small>
                                    <p>Renders the currently found manifest</p>
                                </li>
                                <li>
                                    <h6>Pre-Cache Manifest</h6>
                                    <small><a href="/pwa/precache-manifest">/precache-manifest</a> -- or -- <a href="/pwa/precache">/precache</a> </small>
                                    <p>Renders the currently found precache-manifest</p>
                                </li>
                                <li>
                                    <h6>Service Worker</h6>
                                    <small><a href="/pwa/sw">/sw</a> -- or -- <a href="/pwa/serviceWorker">/serviceWorker</a> -- or -- <a href="/pwa/service-worker">/service-worker</a> </small>
                                    <p>Renders the default service worker</p>
                                </li>
                                <li>
                                    <h6>Registration</h6>
                                    <small><a href="/pwa/js">/js</a> -- or -- <a href="/pwa/register-js">/register-js</a> -- or -- <a href="/pwa/registerJS">/registerJS </a></small>
                                    <p>Renders the JS to register the service worker properly</p>
                                </li>
                            </ul>
                        </article>
                    HTML;
            }

            Debug::dump($result);

            print $result;

            return $pwa;
        }

        throw HyperHttpException::badRequest();
    }

    protected function configUI()
    {
        self::error('Configuration UI is not available');
    }

    /**
     * Web app for the browser
     * @param Route $route
     */
    protected function web(Route $route): void
    {
        $ext = Request::method();
        $route->action = Request::isGet() ? $route->action : ($ext . ucfirst($route->action));

        $reviveController = strtr($route->controller, [ucfirst($route->controllerName) => 'Home']);

        if (class_exists($route->controller) || class_exists($reviveController)) {
            if (method_exists(
                    $route->controller,
                    $ext . Request::$route->action) ||
                method_exists(
                    $route->controller,
                    Request::$route->action) || method_exists($reviveController, $route->controllerName)
            ) {

                if (!class_exists($route->controller) && class_exists($reviveController)) {
                    $route->controller = $reviveController;
                    $route->action = $route->controllerName;
                    $route->controllerName = 'home';
                }

                if (action::of($route->controller, $route->action)) {
                    if ($this->validate($route)) {
                        print call_user_func(
                            [new $route->controller(), $route->action],
                            new Request,
                            ...$route->params
                        );
                        return;
                    }

                    Request::redirectTo(@$this->config->authorize->action ?? 'login',
                        @$this->config->authorize->controller ?? 'auth', null, null, ['return' => Request::path()]);

                    return;
                } elseif (self::$debug) {
                    self::error("Method <b>($route->controller::$route->action)</b> not marked as HTTP action, if you want it to be executed as a view add <b>@action</b> annotation");
                    return;
                }

            } else {
                self::error(HyperHttpException::notFound("Controller action <span style='color: red'>( $route->controller::$route->action )</span> not found"));
                return;
            }
        }

        self::error(HyperHttpException::notFound("Controller <span style='color: red'>( $route->controller )</span> not found"));
    }

    /**
     * Validate route against user auth status
     * @param $route
     * @return bool
     */
    protected function validate($route): bool
    {
        # Initialize validators
        $action = $route->action;
        $controller = $route->controller;
        $controllerAllowedRoles = authorize::of($controller);
        $actionAllowedRoles = authorize::of($controller, $action);

        # Validate request
        if ($actionAllowedRoles === false && $controllerAllowedRoles === false)
            return true;
        else if ($actionAllowedRoles === true || $controllerAllowedRoles === true) {
            return User::isAuthenticated();
        } else {
            $roles = [];

            if (is_string($controllerAllowedRoles))
                $roles = explode('|', $controllerAllowedRoles);

            if (is_string($actionAllowedRoles))
                $roles = explode('|', $actionAllowedRoles);

            return !empty(array_filter($roles, fn($r) => User::isInRole($r)));
        }
    }

    public static function setEventHook($name, $callable)
    {
        $app = self::instance();

        if (!isset($app)) return;

        $app->eventHook->setEventHook($name, $callable);
    }

    #endregion

    public static function getModules()
    {
        $predefined = [
            'main' => 'Controllers\\',
            'api' => 'Controllers\\api\\'
        ];

        $devDefined = @HyperApp::instance()->config->modules;

        if (is_null($devDefined)) return (object)$predefined;

        $merge = array_merge($predefined, (array)$devDefined);

        return (object)$merge;
    }

}
