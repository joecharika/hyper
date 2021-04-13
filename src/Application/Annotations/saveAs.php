<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Application\Annotations;


/**
 * Annotation File
 * Used to mark that a model field is supposed
 * to be treated as file in occasions such as uploading etc.
 * @package Hyper\Annotations
 * @Annotation save-as
 */
class saveAs extends  HyperAnnotation
{
    /**
     * File constructor.
     * @param bool $isFile = true
     */
    public function __construct(bool $isFile = true)
    {
    }

    public static function of(string $className, ?string $fieldOrMethod = null)
    {
        return parent::__of($className, $fieldOrMethod, ['save-as','saveAs','Save-As','SaveAs']);
    }
}