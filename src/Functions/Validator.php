<?php


namespace hyper\Functions;


use Hyper\Reflection\Annotation;

abstract class Validator
{
    function validate(object $object): object
    {
        $class = strtolower(get_class($object));
        $properties = get_class_vars($class);


        $validationAnnotations = [
            'required',
            'length',
            'email',
            'pattern',
        ];

        foreach ($properties as $property => $value) {
            $rules = Annotation::getPropertyAnnotation($class, $property, 'Validate');

            if (isset($rules)) {
                $rules = explode('|', $rules);
                [$ruleName, $rule] = $rules;

                Debug::dump($ruleName);
            }
        }

        return $object;
    }

}