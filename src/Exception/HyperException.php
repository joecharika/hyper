<?php


namespace Hyper\Exception;

use Exception;
use Func\Twig\CompressExtension;
use Hyper\Application\HyperApp;
use Hyper\Functions\Arr;
use Hyper\Functions\Obj;
use Hyper\ViewEngine\Html;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

/**
 * Class HyperException
 * @package hyper\Exception
 */
class HyperException extends Exception
{
    /**
     * @var string
     */
    public $header = "HTTP/1.0 500 Internal server error",
        $code = "500",
        $message = "Internal Server error";

    /**
     * @param null $message
     */
    public function throw($message = null)
    {
        !HyperApp::config()->debug || $this->message = is_null($message) ? $this->message : $message;
        try {
            $this->run();
        } catch (LoaderError $e) {
            print $e->getMessage();
        } catch (RuntimeError $e) {
            print $e->getMessage();
        } catch (SyntaxError $e) {
            print $e->getMessage();
        }
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function run()
    {
        $path = explode("\\", static::class);
        $type = $path[array_key_last($path)];

        $config = HyperApp::config();

        $stackTrace = $config->debug ? $this->getTraceAsString() : '';
        $stackTrace = str_replace('#', '<br>#', $stackTrace);
        $stackTrace = $config->debug
            ? Html::div(
                ''
                . Html::heading('<i>Stacktrace</i>', 5)
                . Html::create('p', [], "# $type")
                . Html::create('pre', ['class' => 'h-exc-code'], $stackTrace)
                . Html::break(),
                ['class="h-exc-stacktrace"']
            )
            : '';

        $file = $config->debug
            ? $config->errors->defaultPath
            : Obj::property($config->errors->custom, $this->code,
                $config->errors->defaultPath);

        $twig = new Environment(new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/views/'));
        $twig->addExtension(new CompressExtension());

        try {
            echo $twig->render($file, [
                'title' => "Error $this->code" . ($config->debug ? " : $type" : ''),
                'message' => $this->message,
                'report' => $config->reportLink ?? "#",
                'returnLink' => Arr::key($_SERVER, "HTTP_REFERER", "/"),
                'website' => Arr::key($_SERVER, "HTTP_HOST", 'unknown_site') . Arr::safeArrayGet($_SERVER, 'PATH_INFO',
                        ''),
                'stackTrace' => $stackTrace,
            ]);
        } catch (LoaderError $e) {
            throw $e;
        } catch (RuntimeError $e) {
            throw $e;
        } catch (SyntaxError $e) {
            throw $e;
        }
        exit(0);
    }
}