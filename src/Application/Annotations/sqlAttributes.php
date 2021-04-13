<?php


namespace Hyper\Application\Annotations;


/**
 * Class sqlAttributes
 * @Annotation sql-attributes
 * @package Hyper\Application\Annotations
 */
class sqlAttributes extends HyperAnnotation
{
    public static function of(string $className, ?string $fieldOrMethod = null)
    {
        return parent::__of(
            $className,
            $fieldOrMethod,
            ['sql-attributes', 'sqlAttributes', 'SqlAttributes']
        );
    }
}