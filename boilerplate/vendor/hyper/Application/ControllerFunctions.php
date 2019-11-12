<?php

namespace Hyper\Application;

use Func\Twig\CompressExtension;
use Hyper\Database\DatabaseContext;
use Hyper\Exception\{HyperException, HyperHttpException, NullValueException};
use Hyper\Functions\Arr;
use Twig\{Environment,
    Error\LoaderError,
    Error\RuntimeError,
    Error\SyntaxError,
    Loader\FilesystemLoader,
    TwigFilter,
    TwigFunction};
use function array_merge;
use function class_exists;
use function explode;
use function is_null;
use function json_encode;
use function str_replace;

/**
 * Trait ControllerFunctions
 * @package hyper\Application
 */
trait ControllerFunctions
{
    /**
     * @var DatabaseContext $db
     * @var string $model
     * @var string $layout
     */
    public $db, $model, $layout, $name;


    /**
     * ControllerFunctions constructor.
     */
    public function __construct()
    {

        $this->name = str_replace('Controller', '', str_replace('Controllers\\', '', static::class));

        $this->model = '\\Models\\' . $this->name;


        if (class_exists($this->model))
            $this->db = new DatabaseContext($this->name);

        if (is_null($this->layout)) $this->layout = 'layout';
    }

    /**
     * @param mixed $data
     */
    public function json($data)
    {
        if (is_null($data)) (new NullValueException)->throw();
        print(json_encode($data));
    }


    public function view(string $view, $model = null, $vars = [])
    {
        $array = explode('.', $view);
        $folder = Arr::safeArrayGet($array, 0, '');
        $view = Arr::safeArrayGet($array, 1, '');

        $loader = new FilesystemLoader('views/');
        $twig = new Environment($loader);
        $twig->addExtension(new CompressExtension());

        $this->addTwigFilters($twig);
        $this->addTwigFunctions($twig);

        try {
            echo $twig->render($folder . '/' . $view . '.html.twig',
                array_merge(
                    [
                        'model' => $model,
                        'notification' => (object)Request::notify(),
                        'user' => HyperApp::$user,
                        'request' => (object)[
                            'url' => Request::url(),
                            'protocol' => Request::protocol(),
                            'path' => Request::path(),
                            'previousUrl' => Request::previousUrl(),
                            'query' => Request::query(),
                        ],
                    ],
                    $vars,
                )
            );
        } catch (LoaderError $e) {
            (new HyperHttpException)->notFound($e->getMessage());
        } catch (RuntimeError $e) {
            (new HyperException)->throw($e->getMessage());
        } catch (SyntaxError $e) {
            (new HyperException)->throw($e->getMessage());
        }

    }

    #region Extending Twig

    private function addTwigFilters(Environment &$twig)
    {
        #Cast object to array
        $twig->addFilter(new TwigFilter('toArray', function ($object) {
            return (array)$object;
        }));

        #Cast array to object
        $twig->addFilter(new TwigFilter('toObject', function ($array) {
            return (object)$array;
        }));

        #Cast array to object
        $twig->addFilter(new TwigFilter('isArray', function ($array) {
            return is_array($array);
        }));

        $twig->addFilter(new TwigFilter('toPascal', 'Str::toPascal'));
        $twig->addFilter(new TwigFilter('toCamel', 'Str::toCamel'));
    }

    private function addTwigFunctions(Environment &$twig)
    {
        $twig->addFunction(new TwigFunction('img', function ($image) {
            return Request::protocol() . '://' . Request::server() . '/assets/img/' . $image;
        }));
        $twig->addFunction(new TwigFunction('css', function ($stylesheet) {
            return Request::protocol() . '://' . Request::server() . '/assets/css/' . $stylesheet;
        }));
        $twig->addFunction(new TwigFunction('js', function ($script) {
            return Request::protocol() . '://' . Request::server() . '/assets/js/' . $script;
        }));
        $twig->addFunction(new TwigFunction('asset', function ($asset) {
            return Request::protocol() . '://' . Request::server() . '/assets/' . $asset;
        }));
    }

    #endregion

}
