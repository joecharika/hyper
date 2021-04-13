<?php


namespace Hyper\Application\Annotations {

    use Hyper\Reflection\Annotation;
    use function method_exists;
    use function property_exists;

    class HyperAnnotation
    {
        /**
         * @param string $className
         * @param string|null $fieldOrMethod
         * @return string|null
         */
        static function of(string $className, ?string $fieldOrMethod = null)
        {
            return self::__of($className, $fieldOrMethod, []);
        }

        /**
         * @param string $className
         * @param string|null $fieldOrMethod
         * @param array $selectors
         * @param false $propertiesOnly
         * @param false $methodsOnly
         * @return string|bool|null
         */
        static function __of(string $className, ?string $fieldOrMethod, array $selectors, $propertiesOnly = false, $methodsOnly = false)
        {
            if (is_null($fieldOrMethod)) {
                foreach ($selectors as $selector) {
                    $value = Annotation::getClassAnnotation(
                        $className,
                        $selector
                    );

                    if (!is_null($value)) return $value;

                }

            }

            $value = null;

            if (!$propertiesOnly && method_exists($className, $fieldOrMethod))
                foreach ($selectors as $selector) {
                    $value ??= Annotation::getMethodAnnotation(
                        $className,
                        $fieldOrMethod,
                        $selector
                    );

                    if (!is_null($value)) break;
                }

            if (!$methodsOnly && property_exists($className, $fieldOrMethod))
                foreach ($selectors as $selector) {
                    $value ??= Annotation::getPropertyAnnotation(
                        $className,
                        $fieldOrMethod,
                        $selector
                    );

                    if (!is_null($value)) break;
                }

            return $value;
        }

    }
}