<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application;


use Hyper\Database\DatabaseContext;
use Hyper\Functions\{Obj};
use Hyper\Http\Cookie;
use Hyper\Models\{Claim, User};
use Hyper\Utils\{General, Generator};

/**
 * Class Authorization
 * @package Hyper\Application
 */
class Authorization
{
    /** @var string $token */
    private $token;

    /** @var User */
    private $user;

    /** @var DatabaseContext */
    private $claims;

    /** @var DatabaseContext */
    private $users;

    /** @var string */
    private $cryptoAlgorithm = 'whirlpool';

    /** @var Cookie */
    private $cookie;

    /**
     * Authorization constructor.
     */
    public function __construct()
    {
        $this->users = new DatabaseContext('user');
        $this->claims = new DatabaseContext('claim');
        $this->cookie = new Cookie();

        ['token' => $this->token, 'user' => $this->user] = (array)$this->getSession();
    }

    /**
     * @return object|NULL
     */
    public function getSession()
    {
        if (isset($this->token) && isset($this->user)) {
            $token = $this->token;
            $user = $this->user;
        } else {
            $token = $this->token = $this->cookie->getCookie('__user');

            /** @var Claim $claim */
            $claim = empty($token) ? null : $this->claims->first('token', $token);

            # Session deleted remotely or has expired
            if (!isset($claim)) {
                $this->cookie->removeCookie('__user');
                return (object)[
                    'token' => null,
                    'user' => null,
                ];
            }

            if (empty(HyperApp::$storage['userClaim']))
                HyperApp::$storage['userClaim'] = $claim;

            HyperApp::$user = $user = $this->user = Obj::property($claim, 'user',
                $this->users->first('id', $claim->userId ?? ''));
        }

        return (object)[
            'token' => $token,
            'user' => $user
        ];
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Logout currently logged in user
     * @return bool
     */
    public function logout(): bool
    {
        $this->user = null;
        @HyperApp::$storage['userClaim']->state = 0;

        return $this->cookie->removeCookie('__user')
            && $this->claims->delete(HyperApp::$storage['userClaim']);
    }

    /**
     * Register a new user with username and password
     * @param string $username
     * @param string $password
     * @param string $role
     * @return User|string
     */
    public function register(string $username, string $password, $role = 'default')
    {
        $user = new User($username);
        $exists = $this->users->first('username', $username);

        if (isset($exists)) return "Username '$username' is already taken";

        if (strlen($password) < 8) return 'Password must be at least 8 characters long';

        $user->id = uniqid();
        $user->salt = uniqid();
        $user->key = $this->encrypt($password, $user->salt);
        $user->name = $username;
        $user->role = $role;

        if ($this->users->add($user))
            return $this->login($username, $password);

        return 'Registration failed';
    }

    /**
     * Generate a PBKDF2 key derivation of a supplied password
     * @param string $password
     * @param string $salt
     * @return mixed
     */
    public function encrypt(string $password, $salt = null): string
    {
        return hash_pbkdf2($this->cryptoAlgorithm, $password, $salt ?? uniqid(), 7);
    }

    /**
     * Sign in a user with username and password
     * @param string $username
     * @param string $password
     * @return User|string
     */
    public function login(string $username, string $password)
    {
        #Get user from the database
        $this->user = $this->users->first('username', $username);

        if (isset($this->user)) {
            if ($this->user->lockedOut) return 'Your account has been disabled. Contact admin for more';
            if ($this->user->key === $this->encrypt($password, $this->user->salt)) {
                if ($this->createSession($this->user))
                    return HyperApp::$user = $this->user;
            } else return 'Password is incorrect';
        }

        return 'User is not registered';
    }

    /**
     * Create a new session
     * @param User $user
     * @return bool True if the session update was accepted,
     */
    private function createSession(User $user): bool
    {
        $newToken = Generator::token($user->id);

        # Create a new login claim
        $update = $this->claims
            ->add((new Claim())
                ->setId(uniqid())
                ->setToken($newToken)
                ->setUserId($user->id)
                ->setBrowser(General::browser())
                ->setIPAddress(General::ipAddress())
                ->setState(true)
            );

        # Check if the update was accepted or not
        if ($update) {
            # Save user.id to session
            $this->cookie->addCookie('__user', $newToken, 0, '/');
        }

        return $update;
    }

}
