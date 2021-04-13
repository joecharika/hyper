<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\SQL;


/**
 * Class SQLAttributes
 * <p>Defines sql attributes for a property for example primary key specification</p>
 * <p>Usage: @SQLAttributes primary key not null, etc</p>
 * @package Hyper\SQL
 * @Annotation
 */
class SQLAttributes
{
    /**
     * SQLAttributes constructor.
     * @param string|array $attributes
     */
    public function __construct($attributes)
    {
    }
}