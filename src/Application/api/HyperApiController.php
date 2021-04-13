<?php


namespace Hyper\Application\api {


    use Hyper\Application\Controllers\TControllerContext;

    class HyperApiController
    {
        use TControllerContext;

        public function ok($data): HyperApiResponse
        {
            return new HyperApiResponse($data);
        }

        public function statusCode($code, $data): HyperApiResponse
        {
            return new HyperApiResponse($data, $code);
        }

        public function created($data): HyperApiResponse
        {
            return new HyperApiResponse($data, 201);
        }

        public function notFound(): HyperApiResponse
        {
            return HyperApiResponse::NotFoundResponse();
        }

        public function internalServerError($error): HyperApiResponse
        {
            return HyperApiResponse::ErrorResponse($error);
        }

        public function unauthorised(): HyperApiResponse
        {
            return HyperApiResponse::UnauthorisedResponse();
        }
    }
}