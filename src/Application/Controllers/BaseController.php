<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Controllers;

use Exception;
use Func\Twig\CompressExtension;
use Hyper\Application\{HyperApp, HyperEventHook};
use Hyper\Database\DatabaseContext;
use Hyper\Exception\{HyperError, HyperException, HyperHttpException, NullValueException};
use Hyper\Files\Folder;
use Hyper\Functions\{Str};
use Hyper\Http\HttpMessage;
use Hyper\Http\Request;
use Hyper\Twig\TwigFilters;
use Hyper\Twig\TwigFunctions;
use Hyper\Utils\FormBuilder;
use Hyper\Utils\Html;
use Twig\{Environment, Error\Error, Error\LoaderError, Loader\FilesystemLoader};
use Twig_Extensions_Extension_Array;
use function array_merge;
use function class_exists;
use function is_null;
use function json_encode;
use function str_replace;

/**
 * Class BaseController
 * @package hyper\Application
 */
class BaseController
{
    use HyperError;

    public
        /** @var DatabaseContext */
        $db,
        /** @var string */
        $model,
        /** @var string */
        $modelName,
        /** @var string */
        $name;

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        $this->name = $this->name ?? strtr(static::class, ['Controllers\\' => '', 'Controller' => '']);
        $this->model = $this->model ?? '\\Models\\' . Str::singular($this->name);
        $this->modelName = $this->modelName ?? Str::singular($this->name);

        if (class_exists($this->model))
            $this->db = new DatabaseContext($this->modelName);

    }

    /**
     * Convert $data to json and print it out
     * @param mixed $data
     * @return false|string
     */
    public function json($data)
    {
        if (is_null($data)) self::error(new NullValueException);
        return json_encode($data);
    }


    /**
     * @param string $view
     * @param null $model
     * @param string|HttpMessage|null $message
     * @param array $vars
     * @return string
     */
    public function view(string $view, $model = null, $message = null, $vars = [])
    {
        try {
            $view = strtolower(str_replace('.', '/', $view));
            $twig = new Environment(new FilesystemLoader(Folder::views()));

            HyperApp::event(HyperEventHook::renderingStarting, $twig);

            $this->addTwigExtensions($twig);
            TwigFilters::attach($twig);
            TwigFunctions::attach($twig);

            return $twig->render("$view.html.twig",
                array_merge(
                    [
                        'model' => $model,
                        'user' => HyperApp::$user,
                        'request' => (object)array_merge([
                            'url' => Request::url(),
                            'protocol' => Request::protocol(),
                            'path' => Request::path(),
                            'previousUrl' => Request::previousUrl(),
                            'query' => Request::query(),
                            'params' => Request::params()
                        ]),
                        'app' => HyperApp::instance(),
                        'appStorage' => HyperApp::$storage,
                        'route' => Request::route(),
                        'notification' => $message instanceof HttpMessage
                            ? $message
                            : new HttpMessage($message),
                        'html' => new Html(true),
                    ],
                    $vars
                )
            );

        } catch (LoaderError $e) {
            self::error(HyperHttpException::notFound($e->getMessage()));
        } catch (Error $e) {
            self::error(new HyperException($e->getMessage() . ' on line: ' . $e->getLine() . ' in ' . $e->getFile()));
        } catch (Exception $e) {
            self::error(new HyperException($e->getMessage() . ' on line: ' . $e->getLine() . ' in ' . $e->getFile()));
        }

        return 'An error occurred while processing your request';
    }

    #region Extending Twig

    /**
     * @param Environment $twig
     */
    public function addTwigExtensions(Environment &$twig)
    {
        $twig->addExtension(new CompressExtension());
        $twig->addExtension(new Twig_Extensions_Extension_Array());
    }

    #endregion
}
