<?php

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
use function Hyper\Functions\guid;
use function implode;
use function preg_match_all;
use function trim;
use function uniqid;

/**
 * Class Annotation
 * @package hyper\Reflection
 */
class Annotation
{
    /**
     * @param $class
     * @param $annotation
     * @return string|null
     */
    public static function getClassAnnotation($class, $annotation)
    {
        return Arr::safeArrayGet(self::getClassAnnotations($class), $annotation, null);
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
        foreach (Arr::safeArrayGet($matches, 1, []) as $annotation) {
            $line = explode(" ", $annotation);
            $key = trim(Arr::safeArrayGet($line, 0, uniqid()));
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
        return Arr::safeArrayGet(self::getMethodAnnotations($class, $methodName), $annotation, null);
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
        $annotation = Arr::safeArrayGet(self::getPropertyAnnotations($class, $propertyName), $annotation);
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
