<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application;


use Exception;
use Hyper\Application\Http\Cookie;
use Hyper\Functions\{Obj};
use Hyper\Models\{Claim, User};
use Hyper\SQL\Database\DatabaseContext;
use Hyper\Utils\{General};
use function base64_encode;
use function time;

/**
 * Class Authorization
 * @package Hyper\Application
 */
class Authorization
{
    private ?DatabaseContext $claims, $users;
    private ?string $token, $cryptoAlgorithm = PASSWORD_DEFAULT;
    private ?Cookie $cookie;

    private $user;

    /**
     * Authorization constructor.
     */
    public function __construct()
    {
        $this->users = new DatabaseContext('User');
        $this->claims = new DatabaseContext('Claim');
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

        return (object)['token' => $token, 'user' => $user];
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
     * @throws Exception
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
     * @param bool $login
     * @return User|string
     * @throws Exception
     */
    public function register(string $username, string $password, $role = 'default', $login = true)
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
            return $login
                ? $this->login($username, $password, $user)
                : $user;

        return 'Registration failed';
    }

    /**
     * Generate a PBKDF2 key derivation of a supplied password
     * @param string $password
     * @param ?string $salt
     * @return mixed
     */
    public function encrypt(string $password, $salt = null): string
    {
        return password_hash($password . $salt, $this->cryptoAlgorithm);
    }

    /**
     * Sign in a user with username and password
     * @param string $username
     * @param string $password
     * @param User|null $user
     * @return User|string
     * @throws Exception
     */
    public function login(string $username, string $password, $user = null)
    {
        #Get user from the database
        $this->user = $user ?? $this->users->first('username', $username);

        if (isset($this->user)) {
            if ($this->user->lockedOut) return 'Your account has been disabled. Contact admin for more';
            if (password_verify($password . $this->user->salt, $this->user->key)) {
                if ($this->createSession($this->user))
                    return HyperApp::$user = $this->user;
                else return 'Sign-in could not be saved, please verify you have allowed cookies';
            } else return 'Password is incorrect';
        }

        return 'User is not registered';
    }

    /**
     * Create a new session
     * @param User $user
     * @return bool True if the session update was accepted,
     * @throws Exception
     */
    private function createSession(User $user): bool
    {
        $ipAddress = General::ipAddress();

        # Reject anonymous IP
        if (!isset($ipAddress)) return false;

        # Create a new login claim
        $claim = $this->claims
            ->add((new Claim)
                ->setId(uniqid())
                ->setToken(sprintf('HiToken_%s', base64_encode(uniqid("$user->username:$user->id"))))
                ->setUserId($user->id)
                ->setBrowser(General::browser())
                ->setIPAddress($ipAddress)
                ->setState(true)
            );

        # Save user.id to session
        return (new Cookie)->addCookie('__user', $claim->token, time() + (86400 * 7), '/');
    }

}
