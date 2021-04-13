<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Models;


use Hyper\SQL\SQLType;

/**
 * Class User
 * @package hyper\Models
 */
class Claim
{

    /**
     * @var string $id
     * @SQLType varchar(128)
     * @SQLAttributes primary key unique not null
     */
    public $id;

    public $token;
    /**
     * @var string $userId
     * @SQLType varchar(128)
     * @SQLAttributes not null
     */
    public $userId;
    /**
     * @var bool
     * @SQLType boolean
     */
    public $state;
    public $browser;
    public $IPAddress;

    /**
     * @param string $id
     * @return Claim
     */
    public function setId(string $id): Claim
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $userId
     * @return Claim
     */
    public function setUserId(string $userId): Claim
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param string $token
     * @return Claim
     */
    public function setToken(string $token): Claim
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @param bool $state
     * @return Claim
     */
    public function setState(bool $state): Claim
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @param mixed $browser
     * @return Claim
     */
    public function setBrowser($browser)
    {
        $this->browser = $browser;
        return $this;
    }

    /**
     * @param mixed $IPAddress
     * @return Claim
     */
    public function setIPAddress($IPAddress)
    {
        $this->IPAddress = $IPAddress;
        return $this;
    }
}