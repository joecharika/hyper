<?php


namespace Hyper\Exception;


use Hyper\Application\HyperApp;

class HyperHttpException extends HyperException
{
    public function notFound($message = "Not found")
    {
        (new HyperHttpException(HyperApp::$debug ? $message : "Not found", "404"))->throw();
    }

    public function badRequest()
    {
        (new HyperHttpException("Bad request", "400.5"))->throw();
    }

    public function notAuthorised()
    {
        (new HyperHttpException("Not authorised", "403"))->throw();
    }
}