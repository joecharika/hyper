<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Functions;


use Hyper\Reflection\Annotation;
use ReflectionClass;
use ReflectionException;

/**
 * Class Validator
 *  Available rules
 *  => required as is<br>
 *  => length as length(len)
 *  => min as min(lower)
 *  => max as max(lower)
 *  => range as range(lower,upper)
 *  => email as is
 *  => pattern as RegEx pattern
 *  => contain as contain(substring)
 *  => endWith as endWith(substring)
 *  => startWith as startWith(substring)
 * @package hyper\Functions
 * @usage @Validator(['rule1','rule2']) eg. @Validator(['required','length|5'])
 */
abstract class Validator
{
    #region Validator Core
    /**
     * @param object $object
     * @param null $class
     * @return object
     * @throws ReflectionException
     */
    public static function validate(object $object, $class = null): object
    {
        $class ??= get_class($object);
        $object = Obj::toInstance($class, $object);
        $errors = [];

        $properties = (new ReflectionClass($class))->getProperties();

        if (empty($properties)) $properties = (array)$object;

        foreach ($properties as $property) {
            if ($property->isPublic()) {
                $value = $property->isInitialized($object) ? $property->getValue($object) : null;

                $propertyAnnotation = Annotation::getPropertyAnnotation($property->getDeclaringClass()->getName(), $property->getName(), 'validator');

                foreach (explode('|', $propertyAnnotation) as $rule) {
                    $rule = trim($rule);

                    if (Str::contains($rule, 'required')) {
                        self::required($value, $errors, $property->getName());
                    } elseif (Str::contains($rule, 'email')) {
                        self::email($value, $errors, $property->getName());
                    } elseif (Str::contains($rule, 'pattern')) {
                        self::pattern($rule, $value, $errors, $property->getName());
                    } elseif (Str::contains($rule, 'length')) {
                        if (strlen($value) !== (int)strtr($rule, ['length(' => '', ')' => '']))
                            $errors[$property->getName()][] = 'Length does not match required';
                    } elseif (Str::contains($rule, 'min')) {
                        $lower = (float)strtr($rule, ['min(' => '', ')' => '']);
                        if (strlen($value) < $lower)
                            $errors[$property->getName()][] = "{$property->getName()} should be greater or equal to $lower";
                    } elseif (Str::contains($rule, 'max')) {
                        $lower = (float)strtr($rule, ['max(' => '', ')' => '']);
                        if (strlen($value) < $lower)
                            $errors[$property->getName()][] = "{$property->getName()} should be less or equal to $lower";
                    } elseif (Str::contains($rule, 'range')) {
                        self::length($rule, $value, $errors, $property->getName());
                    } elseif (Str::contains($rule, 'contain')) {
                        $needle = strtr($rule, ['contain(' => '', ')' => '']);
                        if (!Str::contains($value, $needle))
                            $errors[$property->getName()][] = "Must contain $needle";
                    } elseif (Str::contains($rule, 'endWith')) {
                        $needle = strtr($rule, ['endWith(' => '', ')' => '']);
                        if (!Str::endsWith($value, $needle))
                            $errors[$property->getName()][] = "Must end with $needle";
                    } elseif (Str::contains($rule, 'startWith')) {
                        $needle = strtr($rule, ['startWith(' => '', ')' => '']);
                        if (!Str::startsWith($value, $needle))
                            $errors[$property->getName()][] = "Must start with $needle";
                    }
                }
            }
        }

        return (object)[
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    #endregion

    #region Rules
    /**
     * @param $value
     * @param array $errors
     * @param $property
     */
    protected static function required($value, array &$errors, $property): void
    {
        if (!isset($value) || empty($value)) $errors[$property] = ["$property is required"];
    }

    /**
     * @param $value
     * @param array $errors
     * @param $property
     */
    protected static function email($value, array &$errors, $property): void
    {
        preg_match(
            '/[a-z\d_]+@[a-z\d_]+\.[a-z\d_]+/s',
            $value, $matches
        );
        if (empty($matches)) $errors[$property][] = 'Not a valid email';
    }

    /**
     * @param string $rule
     * @param $value
     * @param array $errors
     * @param $property
     */
    protected static function pattern(string $rule, $value, array &$errors, $property): void
    {
        if (preg_match($rule, $value)) $errors[$property][] = 'Does not match the required pattern';
    }

    /**
     * @param string $rule
     * @param $value
     * @param array $errors
     * @param $property
     */
    protected static function length(string $rule, $value, array &$errors, $property): void
    {
        [$lower, $upper] = explode(',', strtr($rule, ['range(' => '', ')' => '']));
        $testValue = (int)$value;
        if (!($testValue >= (int)$lower && $testValue <= (int)$upper))
            $errors[$property][] = 'Value not in range';
    }
    #endregion
}