<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig;

/**
 * Marks a content as safe.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Markup implements \Countable, \JsonSerializable
{
    private $content;
    private $charset;

    public function __construct($content, $charset)
    {
        $this->content = (string) $content;
        $this->charset = $charset;
    }

    public function __toString()
    {
        return $this->content;
    }

    public function count()
    {
        return mb_strlen($this->content, $this->charset);
    }

    public function jsonSerialize()
    {
        return $this->content;
    }
}

class_alias('Twig\Markup', 'Twig_Markup');
