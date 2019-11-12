<?php
/**
 * hyper v1.0.0-beta.2 (https://hyper.com/php)
 * Copyright (c) 2019. J.Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Notifications;


/**
 * Class HttpMessage
 * @package Hyper\Notifications
 */
class HttpMessage
{
    /**
     * @var string $message
     * @var MessageType $type
     */
    public $message, $type;

    /**
     * HttpMessage constructor.
     * @param $message
     * @param string $type
     */
    public function __construct(string $message, $type = MessageType::INFO)
    {
        $this->message = $message;
        $this->type = $type;
    }

    public function __toString()
    {
        return $this->message;
    }
}