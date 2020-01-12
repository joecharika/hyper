<?php

/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Twig\Error;

/**
 * Exception thrown when an error occurs during template loading.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class LoaderError extends Error
{
}

class_alias('Twig\Error\LoaderError', 'Twig_Error_Loader');
