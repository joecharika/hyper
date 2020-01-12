<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Models;


use Hyper\Annotations\file;
use Hyper\Application\Authorization;
use Hyper\Functions\Debug;
use Hyper\Functions\Obj;
use Hyper\Http\Cookie;
use Hyper\SQL\SQLAttributes;
use function Hyper\Database\db;
use function is_null;

/**
 * Class User
 * @package hyper\Models
 */
class User
{

    /**
     * @var string $id
     * @SQLType varchar(128)
     * @SQLAttributes primary key unique not null
     */
    public $id,

        /**
         * @var string $username
         * @SQLType varchar(128)
         * @SQLAttributes unique not null
         */
        $username,

        $name, $lastName, $otherNames,
        $phone, $email, $notes,
        $role, $key, $salt, $lockedOut = false,
        $isEmailVerified, $isPhoneVerified;

    /**
     * @var string
     * @file
     * @required
     */
    public $image;


    public function __construct($username = null)
    {
        if (isset($username))
            $this->username = $username;
    }

    public static function isAuthenticated(): bool
    {
        $token = (new Cookie)->getCookie('__user');

        return empty($token)
            ? false
            : !is_null(db('claim')->first('token', $token));
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
        return Obj::property((new Authorization)->getSession()->user, 'role', 'visitor');
    }

    public static function getId()
    {
        return Obj::property((new Authorization)->getSession()->user, 'id');
    }

    public function __toString()
    {
        return "$this->name $this->otherNames $this->lastName";
    }
}