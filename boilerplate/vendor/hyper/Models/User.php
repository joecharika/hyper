<?php
/**
 * hyper v1.0.0-beta.2 (https://hyper.com/php)
 * Copyright (c) 2019. J.Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Models;


use Hyper\Application\Authorization;
use Hyper\Functions\Obj;

/**
 * Class User
 * @package hyper\Application
 */
class User
{

    /**
     * @var string $id
     * @SQLType varchar(128)
     * @SQLAttributes primary key unique not null
     */
    public $id;
    public $name;
    public $username;
    public $lastName;
    public $otherNames;
    /**
     * @var string
     * @isFile
     * @UploadAs File
     * @required
     */
    public $image;
    public $phone;
    public $email;
    public $notes;
    public $role;
    public $key;
    public $salt;
    public $lockedOut;
    public $lastLogInToken;
    public $isLoggedIn;
    public $lastLoginBrowser;
    public $lastLoginDate;
    public $lastLoginIP;
    public $isEmailVerified;
    public $isPhoneVerified;

    public function __construct($username = null)
    {
        $this->username = $username;
    }

    public static function isAuthenticated()
    {
        return !is_null((new Authorization)->user);
    }

    public static function isInRole($role)
    {
        return strpos($role, Obj::property((new Authorization)->getSession()->user, 'role')) !== false;
    }

    public static function getName()
    {
        return Obj::property((new Authorization)->getSession()->user, 'name');
    }

    public static function getRole()
    {
        return Obj::property((new Authorization)->getSession()->user, 'role');
    }

    public static function getId()
    {
        return Obj::property((new Authorization)->getSession()->user, 'id');
    }

    public function __toString()
    {
        return $this->name . ' ' . $this->otherNames . ' ' . $this->lastName;
    }
}