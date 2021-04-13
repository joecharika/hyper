<?php


namespace Hyper\Application\api;


use Hyper\Application\Http\StatusCode;
use Hyper\Application\HyperApp;

class HyperApiResponse
{
    private ?HyperApp $app;
    private array $statusPhrases;

    public $data;

    public bool $raw;

    public array $headers;

    public int $statusCode;

    public string $statusPhrase;

    public function __construct($data, $statusCode = 200, $statusPhrase = null, $headers = [], $raw = false)
    {
        $this->app = HyperApp::instance();
        $this->statusPhrases = StatusCode::getAsArray();

        $this->data = $data;
        $this->raw = $raw;
        $this->headers = $this->defaultHeaders($headers);
        $this->statusCode = $statusCode;
        $this->statusPhrase = $statusPhrase ?? $this->statusPhrases[$statusCode];
    }

    private function defaultHeaders($custom = []): array
    {
        $overrides = @$this->app->config->api->headers ?? [];

        return \array_merge([
            'X-Framework' => 'Hyper PHP by Joseph Charika',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'OPTIONS,GET,POST,PUT,DELETE',
//            'Content-Type' => 'application/json; charset=UTF-8',
//            'Access-Control-Max-Age' => '3600',
//            'Access-Control-Allow-Headers' => 'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
        ], $overrides, $custom);
    }

    public static function NotFoundResponse(?string $message = 'Resource could not be found on the server')
    {
        return new HyperApiResponse(['status' => false, 'message' => $message], StatusCode::NOT_FOUND,
            'Not found');
    }

    public static function UnauthorisedResponse(?string $message = 'Authentication failed')
    {
        return new HyperApiResponse(['status' => false, 'message' => $message], StatusCode::FORBIDDEN);
    }

    public static function AuthorisationFailedResponse(?string $message = 'Authentication failed')
    {
        return new HyperApiResponse(['status' => false, 'message' => $message], StatusCode::BAD_REQUEST,
            'Authentication failed');
    }

    public static function ErrorResponse($errorData)
    {
        return new HyperApiResponse(['status' => false, 'message' => $errorData], StatusCode::INTERNAL_SERVER_ERROR);
    }
}