<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use ReflectionClass;

use function class_exists;
use function current;
use function is_string;
use function property_exists;

/**
 * @internal
 */
abstract class Attribute
{
    private function __construct() {}

    /**
     * @param object|class-string $class
     */
    final public static function existsOnClass(string $attribute, object|string $class): bool
    {
        return (is_string($class) && !class_exists($class))
            ? false
            : (bool) current((new ReflectionClass($class))->getAttributes($attribute));
    }

    /**
     * @param object|class-string $class
     */
    final public static function existsOnProperty(string $attribute, object|string $class, string $propertyName): bool
    {
        if (is_string($class) && !class_exists($class)) {
            return false;
        }

        if (!property_exists($class, $propertyName)) {
            return false;
        }

        $property = (new ReflectionClass($class))->getProperty($propertyName);

        return (bool) current($property->getAttributes($attribute));
    }
}
