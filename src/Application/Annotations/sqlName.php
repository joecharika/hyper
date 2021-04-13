<?php


namespace Hyper\Application\Annotations;


/**
 * Class sqlName
 * @Annotation sql-name
 * @package Hyper\Application\Annotations
 */
class sqlName extends HyperAnnotation
{
    /**
     * @param string $className
     * @param ?string $fieldOrMethod
     * @return false|string|null
     */
    public static function of(string $className, string $fieldOrMethod = null)
    {
        return parent::__of(
            $className,
            $fieldOrMethod,
            ['sql-name', 'sqlName', 'name']
        );
    }
}