<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Util;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TemplateDirIterator extends \IteratorIterator
{
    public function current()
    {
        return file_get_contents(parent::current());
    }

    public function key()
    {
        return (string) parent::key();
    }
}

class_alias('Twig\Util\TemplateDirIterator', 'Twig_Util_TemplateDirIterator');
