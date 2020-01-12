<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Http;


/**
 * Class HttpMessage
 * @package Hyper\Notifications
 */
class HttpMessage
{
    public
        /**
         * @var string $message
         */
        $message,

        /**
         * @var string $messageType
         */
        $messageType,

        /**
         * @var string Html to display as icon
         */
        $icon,

        /**
         * @var string Associated url to complete or dismiss notification
         */
        $action;

    /**
     * HttpMessage constructor.
     * @param string|null $message
     * @param string $type
     * @param string|null $action
     */
    public function __construct($message, $type = HttpMessageType::INFO, $action = null)
    {
        $this->message = $message;
        $this->messageType = $type;
        $this->action = $action;
    }

    /*
     * Return HTTP_QUERY compatible string representation of the message
     * @return string
     */
    public function __toString()
    {
        $str = "message=$this->message&messageType=$this->messageType";

        if (isset($this->action))
            $str .= "&action=$this->action";

        return $str;
    }
}