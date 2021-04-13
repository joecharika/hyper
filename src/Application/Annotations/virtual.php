<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application\Annotations;


/**
 * Annotation Virtual
 * Mark model property as virtual meaning it will not be used for as a database column
 * @package Hyper\Annotations
 * @Annotation virtual
 */
class virtual extends HyperAnnotation
{
    public static function of(string $className, ?string $fieldOrMethod = null)
    {
        return self::__of($className, $fieldOrMethod, ['virtual']);
    }
}