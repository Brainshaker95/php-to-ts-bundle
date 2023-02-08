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
    final public static function exists(object|string $class, string $attribute): bool
    {
        return (is_string($class) && !class_exists($class))
            ? false
            : (bool) current((new ReflectionClass($class))->getAttributes($attribute));
    }
}
