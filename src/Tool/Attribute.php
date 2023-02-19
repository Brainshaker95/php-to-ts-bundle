<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use ReflectionClass;

use function class_exists;
use function current;
use function is_string;

/**
 * @internal
 */
abstract class Attribute
{
    /**
     * @param object|class-string $class
     */
    final public static function existsOnClass(string $attribute, object|string $class): bool
    {
        return (is_string($class) && !class_exists($class))
            ? false
            : (bool) current((new ReflectionClass($class))->getAttributes($attribute));
    }
}
