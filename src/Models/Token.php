<?php


namespace Hyper\Models;


class Token
{
    public string $token;
    public User $user;

    public function __construct($token, $user)
    {
        $this->token = $token;
        $this->user = $user;
    }
}