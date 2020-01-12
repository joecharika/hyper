<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig;

/**
 * Holds information about a non-compiled Twig template.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class Source
{
    private $code;
    private $name;
    private $path;

    /**
     * @param string $code The template source code
     * @param string $name The template logical name
     * @param string $path The filesystem path of the template if any
     */
    public function __construct(string $code, string $name, string $path = '')
    {
        $this->code = $code;
        $this->name = $name;
        $this->path = $path;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}

class_alias('Twig\Source', 'Twig_Source');
