<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Reflection;


use Hyper\Functions\Arr;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use function array_splice;
use function explode;
use function implode;
use function preg_match_all;
use function trim;
use function uniqid;

/**
 * Class Annotation
 * @package Hyper\Reflection
 */
abstract class Annotation
{
    /**
     * @param $class
     * @param $annotation
     * @return string|null
     */
    public static function getClassAnnotation(string $class, string $annotation)
    {
        return Arr::key(self::getClassAnnotations($class), $annotation, null);
    }

    /**
     * @param $class
     * @return array|null
     */
    public static function getClassAnnotations($class): array
    {
        try {
            return self::getAnnotations(new ReflectionClass($class));
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * @param ReflectionProperty|ReflectionClass|ReflectionMethod|ReflectionFunction $reflection
     * @return array
     */
    private static function getAnnotations($reflection): array
    {
        $annotations = [];
        preg_match_all("/@(.*?)\n/s", $reflection->getDocComment(), $matches);
        foreach (Arr::key($matches, 1, []) as $annotation) {
            $line = explode(" ", $annotation);
            $key = trim(Arr::key($line, 0, uniqid()));
            $annotations[$key] = trim(implode(" ", array_splice($line, 1)));

            if (is_null($annotations[$key])) $annotations[$key] = true;
            if (empty($annotations[$key])) $annotations[$key] = true;
        }

        return $annotations;
    }

    /**
     * @param $class
     * @param $methodName
     * @param $annotation
     * @return string|null
     */
    public static function getMethodAnnotation($class, $methodName, $annotation)
    {
        return Arr::key(self::getMethodAnnotations($class, $methodName), $annotation, null);
    }

    /**
     * @param $class
     * @param $methodName
     * @return array|null
     */
    public static function getMethodAnnotations($class, $methodName)
    {
        try {
            return self::getAnnotations(new ReflectionMethod($class, $methodName));
        } catch (ReflectionException $e) {
            return [];
        }
    }

    /**
     * @param $class
     * @param $propertyName
     * @param $annotation
     * @return string|null
     */
    public static function getPropertyAnnotation($class, $propertyName, $annotation)
    {
        $annotation = Arr::key(self::getPropertyAnnotations($class, $propertyName), $annotation);
        return isset($annotation) ? trim($annotation) : null;
    }

    /**
     * @param $class
     * @param $propertyName
     * @return array|null
     */
    public static function getPropertyAnnotations($class, $propertyName)
    {
        try {
            return self::getAnnotations(new ReflectionProperty($class, $propertyName));
        } catch (ReflectionException $e) {
            return [];
        }
    }

    /**
     * @param $object
     * @return array|null
     */
    public static function getObjectAnnotations($object)
    {
        return self::getAnnotations(new ReflectionObject($object));
    }

    /**
     * @param $functionName
     * @return array|null
     */
    public static function getFunctionAnnotations($functionName)
    {
        try {
            return self::getAnnotations(new ReflectionFunction($functionName));
        } catch (ReflectionException $e) {
            return null;
        }
    }
}
