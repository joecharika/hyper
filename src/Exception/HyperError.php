<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Exception {


    use Error as PHPErrorAlias;
    use Exception;
    use Hyper\{Application\Http\StatusCode,
        Application\HyperApp,
        Files\Folder,
        Functions\Arr,
        Functions\Logger,
        Functions\Obj,
        Functions\Str,
        Utils\Twig\TwigExtensions
    };
    use RuntimeException;
    use Twig\{Environment,
        Error\Error,
        Error\LoaderError,
        Error\RuntimeError,
        Error\SyntaxError,
        Loader\FilesystemLoader
    };
    use function class_exists;
    use function debug_backtrace;
    use function explode;
    use function highlight_string;
    use function htmlspecialchars;
    use function nl2br;
    use function str_replace;

    /**
     * Trait HyperError
     * @package Hyper\Exception
     */
    trait HyperError
    {
        /**
         * Show user-friendly error page
         * NOTE: UI is only provided if Hyper\Application\HyperApp is available
         * @param RuntimeException|Error|Exception|string $error
         */
        public static function error($error)
        {
            ob_end_clean();

            if (!($error instanceof Exception or $error instanceof PHPErrorAlias or $error instanceof RuntimeException)) {
                $var = @debug_backtrace()[0];

                $error = (new HyperException($error))
                    ->setLine(@$var['line'])
                    ->setFile(@$var['file']);
            }

            # Log error first
            $trace = str_replace("\n", "\n\t\t", $error->getTraceAsString());
            Logger::log($log = <<<TEXT
                {$error->getCode()}: {$error->getMessage()}
                
                ## Stacktrace      ::########################################################
                    {$trace}
                ## EndStacktrace   ::########################################################
            TEXT,
                Logger::ERROR
            );

            # Decide how to present error
            if (class_exists('\\Hyper\\Application\\HyperApp')) {
                $config = HyperApp::config();

                $file = $config->debug
                    ? 'error-debug.html.twig'
                    : Obj::property($config->errors->custom, $error->getCode(), $config->errors->default);

                try {
                    $log = $config->debug
                        ? self::hyperError($error)
                        : self::render($file, $error);
                } catch (Error $e) {
                    print 'Fatal error: ' . $e->getMessage();
                }
            } else $log = nl2br($log);

            print $log;
            return exit(0);
        }

        /**
         * Render hyper error UI
         * @param RuntimeException|Error|Exception $exception
         * @return string
         * @throws LoaderError
         * @throws RuntimeError
         * @throws SyntaxError
         */
        private static function hyperError($exception): string
        {
            $config = @HyperApp::config();
            $file = $config->debug
                ? 'error-debug.html.twig'
                : @$config->errors->default;


            return self::render(
                $file ?? 'undefined_default_error_page',
                $exception,
                nl2br(htmlspecialchars($exception->getTraceAsString() ?? '')),
                ' : ' . static::class
            );
        }

        /**
         * Render error file
         * @param string $file
         * @param RuntimeException|Error|Exception $context
         * @param string $trace
         * @param string $title
         * @return string
         * @throws LoaderError
         * @throws RuntimeError
         * @throws SyntaxError
         */
        public static function render(string $file, $context, $trace = '', $title = ''): string
        {
            $code = $context->getCode();

            \http_response_code($code >= 400 ? $code : 500);

            $config = HyperApp::config();

            $twig = new Environment(new FilesystemLoader(
                    $config->debug || empty((array)$config->errors->custom)
                        ? __DIR__ . '/../views'
                        : Folder::views())
            );

            $twig->addExtension(new TwigExtensions());

            try {
                $isHyper = Str::contains($context->getFile(), 'Hyper\\');

                if ($isHyper)
                    $source = '<div class="page mixin">Source not available</div>';
                else {
                    $lines = highlight_string(file_get_contents($context->getFile()), true);
                    $lines = explode("<br />", $lines);
                    $lines[((int)$context->getLine()) - 1] = "<div style='text-decoration: underline wavy red'>{$lines[((int)$context->getLine()) - 1]}</div>";

                    $source = '';
                    foreach ($lines as $key => $_line) {
                        $source .= '<span style="color: #cccccc">' . ++$key . ".&nbsp</span>$_line<br />";
                    }
                }

                return $twig->render($file, [
                    'title' => "Error {$code}{$title}",
                    'report' => $config->reportLink ?? "#",
                    'returnLink' => Arr::key($_SERVER, "HTTP_REFERER", "/"),
                    'website' => Arr::key($_SERVER, "HTTP_HOST", 'unknown_site') . @$_SERVER['PATH_INFO'],
                    'error' => (object)[
                        'message' => $config->debug ? $context->getMessage() : self::getMessage($context->getCode()),
                        'code' => $context->getCode(),
                        'stackTrace' => $trace,
                        'file' => /*$isHyper ? 'File not available' :*/ $context->getFile(),
                        'line' => /*$isHyper ? '##' :*/ $context->getLine(),
                        'source' => $source
                    ],
                    'debug' => $config->debug ?? HyperApp::$debug
                ]);
            } catch (PHPErrorAlias $e) {
                return $e->getMessage();
            }
        }

        /**
         * Get user message code from Status codes
         * @param int $code
         * @return string
         */
        public static function getMessage(int $code)
        {
            return @StatusCode::getAsArray()[$code] ?? 'Error';
        }
    }
}