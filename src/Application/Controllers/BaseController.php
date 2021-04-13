<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application\Controllers;

use Exception;
use Hyper\{Application\Http\HttpMessage,
    Application\Http\Request,
    Application\HyperApp,
    Application\HyperEventHook,
    Exception\HyperError,
    Exception\HyperException,
    Exception\HyperHttpException,
    Exception\NullValueException,
    Files\Folder,
    Utils\Html,
    Utils\Twig\TwigExtensions
};
use Twig\{Environment, Error\Error, Error\LoaderError, Loader\FilesystemLoader};
use function array_merge;
use function is_null;
use function json_encode;
use function str_replace;

/**
 * Class BaseController
 * @package hyper\Application
 */
class BaseController
{
    use HyperError, TControllerContext;


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
     * Renders static file
     * @param string $fileName
     * @param bool $rogue Set true to search outside project root
     * @return false|string|void
     */
    public function staticFile(string $fileName, bool $rogue = false)
    {
        $file = strtr(($rogue ? '' : Folder::root()) . $fileName, ['/' => DIRECTORY_SEPARATOR, '\\' => DIRECTORY_SEPARATOR]);

        if (!file_exists($file)) return self::error("Static file '$file' was not found");

        return \file_get_contents($file);
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
            $message = $message ?? Request::message();
            $view = strtolower(str_replace('.', '/', $view));
            $twig = new Environment(new FilesystemLoader(Folder::root() . 'templates'));

            HyperApp::event(HyperEventHook::renderingStarting, $twig);

            $twig->addExtension(new TwigExtensions());

            return $twig->render("$view.html.twig",
                array_merge(
                    [
                        'model' => $model,
                        'user' => HyperApp::$user,
                        'request' => new Request,
                        'hyperApp' => HyperApp::instance(),
                        'appStorage' => HyperApp::$storage,
                        'route' => Request::route(),
                        'notification' => ($message instanceof HttpMessage || !isset($message))
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

    public function notFound(): string
    {
        throw HyperHttpException::notFound();
    }

    public function badRequest(): string
    {
        throw HyperHttpException::badRequest();
    }

    public function serverError(): string
    {
        throw new HyperHttpException('Internal Server Error', 500);
    }

    public function unauthorised(): string
    {
        throw HyperHttpException::notAuthorised();
    }

}
