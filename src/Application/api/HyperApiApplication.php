<?php


namespace Hyper\Application\api;

use Hyper\{Application\Annotations\action,
    Application\Annotations\authorize,
    Application\Http\Request,
    Application\Http\StatusCode,
    Application\HyperApp,
    Application\Routing\Route,
    Functions\Debug,
    Functions\Logger,
    Models\User};
use function header;
use function http_response_code;
use function is_null;

class HyperApiApplication
{
    private ?HyperApiResponse $response;

    public function __construct(Route $route)
    {
//        HyperApp::setEventHook(HyperEventHook::error, function ($event) {
//
//            $this->response = HyperApiResponse::ErrorResponse([
//                'error' => $event->data,
//                'trace' => debug_backtrace()
//            ]);
//
//            $this->prepareResponse();
//        });
//        $this->verifyClient($route);

        switch ($route->controllerName) {
            case 'login':
            case 'register':
                $this->authorize($route);
                break;
            default :
                $this->request($route);
        }

        $this->prepareResponse();
    }

    protected function verifyClient(Route $route)
    {
//        if($route->controllerName == 'token')
//
//
//        $request = new Request();
//
//        $accessToken =

    }

    protected function authorize(Route $route)
    {
        $request = new Request();
        $response = null;
        $auth = new HyperApiAuthController();

        if ($route->controllerName == 'login')
            $response = $auth->login($request->data->username, $request->data->password);
        else
            $response = $auth->register($request->data->username, $request->data->password);

        if (is_string($response))
            $this->response = HyperApiResponse::AuthorisationFailedResponse();

        else $this->response = new HyperApiResponse(['status' => true, 'data' => $response]);

    }

    protected function request(Route $route)
    {
        $request = new Request;

        $route->action = strtolower($request->requestMethod) . $route->action;
        $this->response = new HyperApiResponse('', StatusCode::NO_CONTENT);

        if (class_exists($route->controller)) {
            if (method_exists($route->controller, $route->action)) {

                if (action::of($route->controller, $route->action)) {
                    if ($this->authorizeRequest($route)) {
                        $action = $route->action;

                        if ($request->requestMethod === 'get' || $request->requestMethod === 'delete')
                            return $this->response = call_user_func(
                                [new $route->controller(), $action],
                                new Request(),
                                ...$route->params
                            );

                        return $this->response = call_user_func(
                            [new $route->controller(), $action],
                            $request, $request->data, ...$route->params
                        );
                    }

                    return $this->response = HyperApiResponse::UnauthorisedResponse();

                } elseif (HyperApp::$debug) {
                    return $this->response = HyperApiResponse::NotFoundResponse("Method ($route->controller::$route->action) not marked as HTTP action, if you want it to be executed as a view add @action annotation");
                }

            } else
                return $this->response = HyperApiResponse::NotFoundResponse("Controller action ( $route->controller::$route->action ) not found");

        }
        return $this->response = HyperApiResponse::NotFoundResponse("Controller ( $route->controller ) not found");
    }

    protected function authorizeRequest(Route $route): bool
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
//        if (!isset($actionAllowedRoles) && !isset($controllerAllowedRoles))
//            return true;
//        else {
//
//            $roles = isset($actionAllowedRoles)
//                ? explode('|', $actionAllowedRoles)
//                : (!isset($controllerAllowedRoles) ? [] : explode('|', $controllerAllowedRoles));
//
//
//            $claim = (new DatabaseContext(Claim::class))->first('token', Arr::key(Request::headers(), 'token'),
//                SqlOperator::equal, [User::class]);
//
//            if (!isset($claim)) return false;
//
//            $user = $claim->user;
//
//            if ($roles == [true])
//                return true;
//
//            if (array_search(isset($user) ? $user->role : $user->getRole(), $roles) !== false)
//                return true;
//        }
        return false;
    }

    protected function handleToken(): User
    {
        $request = new Request();

        $headers = $request->headers;

        return new User();
    }

    protected function prepareResponse()
    {
        if (is_null($this->response))
            $this->response = new HyperApiResponse('', StatusCode::NO_CONTENT);

        http_response_code($this->response->statusCode);

        foreach ($this->response->headers as $header => $value) {
            header("$header: $value");
        }

        if ($this->response->raw)
            print $this->response->data;
        else
            print json_encode($this->response->data, 0, 1);
    }
}