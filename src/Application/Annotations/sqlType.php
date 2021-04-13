<?php


namespace Hyper\Application\Annotations;


/**
 * Class sqlType
 * @Annotation sql-type
 * @package Hyper\Application\Annotations
 */
class sqlType extends HyperAnnotation
{
    public static function of(string $className, ?string $fieldOrMethod = null)
    {
        return parent::__of(
            $className,
            $fieldOrMethod,
            ['sql-type', 'SqlType','SQLType']
        );
    }
}