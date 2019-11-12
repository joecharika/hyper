<?php
/**
 * hyper v1.0.0-beta.2 (https://hyper.com/php)
 * Copyright (c) 2019. J.Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application;


use DateInterval;
use DateTime;
use Exception;
use Hyper\Database\DatabaseContext;
use Hyper\Functions\Arr;
use Hyper\Models\User;

/**
 * Class Authorization
 * @package hyper\Application
 */
class Authorization
{
    /** @var string */
    public $token;

    /** @var User */
    public $user;

    /** @var DatabaseContext */
    private $db;

    private $cryptoAlgorithm = "whirlpool";

    public function __construct()
    {
        $this->db = new DatabaseContext('user');

        if (session_status() !== 2) session_start();
        $this->restoreSession();
    }

    private function restoreSession()
    {
        $this->user = $this->getSession()->user;
        $this->token = $this->getSession()->token;
    }

    public function getSession()
    {
        if (session_status() !== 2) return null;

        return (object)[
            "id" => session_id(),
            "token" => $this->token,
            "user" => $this->db->firstById(Arr::safeArrayGet($_SESSION, "user", 0)),
            "expiryDate" => $this->getExpiryDate(),
        ];
    }

    /**
     * @return DateTime|false|null
     */
    public function getExpiryDate()
    {
        try {
            return date_add(new DateTime(), new DateInterval(session_cache_expire()));
        } catch (Exception $exc) {
            return null;
        }
    }

    public function logout()
    {
        $this->user = null;
        $this->destroySession();
    }

    private function destroySession()
    {
        unset($_SESSION["user"]);
        if (session_start() === 2) session_destroy();
    }

    public function register(string $username, string $password, $role = "default")
    {
        $user = new User($username);

        $user->id = uniqid();
        $user->salt = uniqid();
        $user->key = $this->encrypt($password, $user->salt);
        $user->name = $username;
        $user->role = $role;

        if ($this->db->insert($user))
            $this->login($username, $password);
    }

    /**
     * @param string $password
     * @param string $salt
     * @return mixed
     */
    public function encrypt(string $password, $salt = null)
    {
        $salt = isset($salt) ? $salt : uniqid();
        return hash_pbkdf2($this->cryptoAlgorithm, $password, $salt, 7);
    }

    public function login(string $username, string $password)
    {
        $this->user = new User($username);

        $this->user = $this->db->first(function ($user) {
            return $user->username === $this->user->username;
        });

        if (!is_null($this->user)) {
            if ($this->user->key === $this->encrypt($password, $this->user->salt)) {
                $this->createSession($this->user);
                HyperApp::$user = $this->user;
                return $this->user;
            } else return "Password is incorrect";
        }
        return "User does not exist";
    }

    private function createSession($user): bool
    {

        $_SESSION["user"] = $user->id;

        $user->lastLoginIP = $this->getUserIpAddr();
        $user->lastLoginDate = date('Y-m-d h:m:s');
        $user->lastLoginBrowser = Arr::safeArrayGet($_SERVER, 'HTTP_USER_AGENT', 'Unknown browser/User agent');

        return $this->db->update($user);
    }

    public function getUserIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
