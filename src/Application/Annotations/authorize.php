<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application\Annotations {


    /**
     * Annotation Authorize
     * Used to mark a controller or an action as one that requires authorization
     * @package Hyper\Annotations
     * @Annotation authorize
     */
    class authorize extends HyperAnnotation
    {
        public static function of(string $className, ?string $fieldOrMethod = null)
        {
            return self::__of($className, $fieldOrMethod, ['authorize', 'authorise', 'auth'], false, true);
        }
    }
}