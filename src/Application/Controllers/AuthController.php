<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Controllers;

use Hyper\Annotations\action;
use Hyper\Annotations\authorize;
use Hyper\Application\Authorization;
use Hyper\Controllers\Controller;
use Hyper\Database\DatabaseContext;
use Hyper\Http\{HttpMessage, HttpMessageType, Request};
use Hyper\Functions\Debug;
use Models\User;

/**
 * Class AuthController
 * @package Controllers
 */
class AuthController extends Controller
{
    /**
     * @var Authorization $auth
     */
    protected $auth;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->auth = new Authorization();
        $this->db = new DatabaseContext('user');
    }

    /**
     * @action
     * @return string
     */
    public function index()
    {
        return Request::redirectTo('signIn', 'auth', null, null);
    }

    /**
     * @authorize
     * @action
     * @return string
     */
    public function signOut()
    {
        return Request::redirectTo(
            'index',
            'home',
            null,
            $this->auth->logout()
                ? 'Logged out'
                : 'Failed to logout, please try again'
        );
    }


    /**
     * @action
     * @return string
     */
    public function browserLogout()
    {
        return Request::redirectToUrl(
            Request::previousUrl(),
            (new DatabaseContext('claim'))->delete(Request::params()->id) ? 'Logged out' : 'Failed to logout remote browser'
        );
    }

    /**
     * @action
     * @param Request $request
     * @param null $model
     * @param null $message
     * @return string
     */
    public function signUp(Request $request, $model = null, $message = null)
    {
        return $this->view('auth.register', $model, $message);
    }

    /**
     * @action
     * @param Request $request
     * @return string
     */
    public function postSignUp(Request $request)
    {
        $data = $request->data;

        $result = "Username \"{$data->username}\" is already taken";

        if (empty($this->db->where('username', $data->username)->toList())) {

            $result = $this->auth->register($data->username, $data->subject);

            if (is_object($result)) {

                $result->email = @$data->email;

                $this->db->update($result);

                return $request->redirectTo(
                    'index',
                    'home',
                    null,
                    'Your registration was successful. Please verify your email or you will not be able to login next time.'
                );
            }
        }

        return $this->signUp($request, $data, new HttpMessage($result, HttpMessageType::WARNING));
    }

    /**
     * @action
     * @param Request $request
     * @param null $model
     * @param null $message
     * @return string
     */
    public function signIn(Request $request, $model = null, $message = null)
    {
        if (User::isAuthenticated())
            return $request->redirectTo('index', 'dashboard', null, $message);

        return $this->view('auth.login', $model, $message);
    }

    /**
     * @action
     * @param Request $request
     * @return string
     */
    public function postSignIn(Request $request)
    {
        $data = Request::data();

        $result = User::isAuthenticated()
            ? $this->auth->getUser()
            : $this->auth->login($data->username, $data->password);

        if ($result instanceof \Hyper\Models\User)
            return Request::redirectToUrl(@Request::get()->return ?? '/dashboard', "Welcome {$this->auth->getUser()}");

        return $this->signIn($request, $data, new HttpMessage($result, HttpMessageType::DANGER));
    }


    /**
     * @action
     * @authorize
     * @return string
     */
    public function password()
    {
        return $this->profile();
    }

    /**
     * @action
     * @authorize
     * @return string
     */
    public function postPassword()
    {
        $model = $this->db->first('id', User::getId());
        $oldPassword = $model->key;
        $newPassword = $model->key = $this->auth->encrypt(Request::data()->n_password, $model->salt);

        if ($oldPassword === $this->auth->encrypt(Request::data()->o_password, $model->salt)) {
            if (Request::data()->n_password === Request::data()->c_password) {
                if ($oldPassword !== $newPassword) {
                    if ($this->db->update($model))
                        return Request::redirectTo(
                            'password',
                            'auth',
                            null,
                            new HttpMessage('Successfully updated your password', HttpMessageType::SUCCESS),
                            );

                    return Request::redirectTo(
                        'password',
                        'auth',
                        null,
                        new HttpMessage('Failed to update your password', HttpMessageType::DANGER)
                    );
                } else
                    return Request::redirectTo(
                        'password',
                        'auth',
                        null,
                        new HttpMessage('New password can\'t be the same as old password', HttpMessageType::WARNING)
                    );
            } else return Request::redirectTo(
                'password',
                'auth',
                null,
                new HttpMessage('Failed to confirm password', HttpMessageType::DANGER)
            );
        }

        return Request::redirectTo(
            'password',
            'auth',
            null,
            new HttpMessage('Password is incorrect', HttpMessageType::DANGER)
        );
    }

    /**
     * @action
     * @authorize
     * @return string
     */
    public function profile()
    {
        return $this->view(
            'auth.profile',
            $this->db->first('id', User::getId()),
            null,
            [
                'claims' => (new DatabaseContext('claim'))
                    ->where('userId', User::getId())
                    ->toList()
            ]
        );
    }

    /**
     * @action
     * @authorize
     * @param Request $request
     * @return string
     */
    public function postProfile(Request $request)
    {
        return $request->redirectTo(
            'profile',
            'auth',
            null,
            $this->db->update($request->bind($this->db->first('id', User::getId())))
                ? 'Successfully updated your profile'
                : 'Failed to update your profile'
        );
    }
}
