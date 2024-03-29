<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Brainshaker95\PhpToTsBundle\Exception\AssertionFailedException;
use ReflectionClass;

use const FILTER_VALIDATE_INT;

use function array_filter;
use function array_map;
use function count;
use function filter_var;
use function in_array;
use function is_a;
use function is_array;
use function is_numeric;
use function is_string;
use function sprintf;

/**
 * @internal
 */
final class Assert
{
    private function __construct() {}

    /**
     * @phpstan-assert non-empty-string $value
     *
     * @return non-empty-string
     */
    public static function nonEmptyStringNonNullable(mixed $value): string
    {
        if (!is_string($value) || !$value) {
            throw new AssertionFailedException(sprintf(
                'Expected value to be a non empty string. Given value was: %s',
                Str::displayType($value),
            ));
        }

        return $value;
    }

    /**
     * @phpstan-assert ?non-empty-string $value
     *
     * @return ?non-empty-string
     */
    public static function nonEmptyStringNullable(mixed $value): ?string
    {
        if ($value === null) {
            return $value;
        }

        return self::nonEmptyStringNonNullable($value);
    }

    /**
     * @phpstan-assert int<0,max> $value
     *
     * @return int<0,max>
     */
    public static function nonNegativeIntegerNonNullable(mixed $value): int
    {
        $intval = is_numeric($value) ? (int) $value : -1;

        if (filter_var($value, FILTER_VALIDATE_INT) === false || $intval < 0) {
            throw new AssertionFailedException(sprintf(
                'Expected value to be a non negative integer. Given value was: %s',
                Str::displayType($value),
            ));
        }

        return $intval;
    }

    /**
     * @phpstan-assert ?int<0,max> $value
     *
     * @return ?int<0,max>
     */
    public static function nonNegativeIntegerNullable(mixed $value): ?int
    {
        if ($value === null) {
            return $value;
        }

        return self::nonNegativeIntegerNonNullable($value);
    }

    /**
     * @phpstan-assert non-empty-string[] $value
     *
     * @return non-empty-string[]
     */
    public static function nonEmptyStringArrayNonNullable(mixed $value): array
    {
        if (!is_array($value)
            || count(array_filter($value, static fn (mixed $val) => !is_string($val) || !$val))) {
            throw new AssertionFailedException(sprintf(
                'Expected value to be a non empty string array. Given value was: %s',
                Str::displayType($value),
            ));
        }

        return $value;
    }

    /**
     * @phpstan-assert ?non-empty-string[] $value
     *
     * @return ?non-empty-string[]
     */
    public static function nonEmptyStringArrayNullable(mixed $value): ?array
    {
        if ($value === null) {
            return $value;
        }

        return self::nonEmptyStringArrayNonNullable($value);
    }

    /**
     * @template T of string[]
     *
     * @phpstan-assert value-of<T> $value
     *
     * @param T $allowedStrings
     *
     * @return value-of<T>
     */
    public static function inStringArrayNonNullable(mixed $value, array $allowedStrings): string
    {
        if (!is_string($value) || !in_array($value, $allowedStrings, true)) {
            throw new AssertionFailedException(sprintf(
                'Expected value to be contained in array %s. Given value was: %s',
                Str::displayType($allowedStrings),
                Str::displayType($value),
            ));
        }

        return $value;
    }

    /**
     * @template T of string[]
     *
     * @phpstan-assert ?value-of<T> $value
     *
     * @param T $allowedStrings
     *
     * @return ?value-of<T>
     */
    public static function inStringArrayNullable(mixed $value, array $allowedStrings): ?string
    {
        if ($value === null) {
            return $value;
        }

        return self::inStringArrayNonNullable($value, $allowedStrings);
    }

    /**
     * @template T of object
     *
     * @phpstan-assert T $value
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function instanceOf(object $value, string $class): object
    {
        if (!$value instanceof $class) {
            throw new AssertionFailedException(sprintf(
                'Expected object to be an instance of "%s". Given instance was of class "%s".',
                $class,
                $value::class,
            ));
        }

        return $value;
    }

    /**
     * @template T of object
     *
     * @phpstan-assert class-string<T> $value
     *
     * @param class-string<T> $class
     *
     * @return class-string<T>
     */
    public static function interfaceClassStringNonNullable(mixed $value, string $class): string
    {
        if (!is_string($value)
            || !is_a($value, $class, true)
            || !(new ReflectionClass($value))->implementsInterface($class)) {
            throw new AssertionFailedException(sprintf(
                'Expected value to be a class string of a class that implements %s. Given value was: %s',
                Str::displayType($value),
                $class,
            ));
        }

        return $value;
    }

    /**
     * @template T of object
     *
     * @phpstan-assert ?class-string<T> $value
     *
     * @param class-string<T> $class
     *
     * @return ?class-string<T>
     */
    public static function interfaceClassStringNullable(mixed $value, string $class): ?string
    {
        if ($value === null) {
            return $value;
        }

        return self::interfaceClassStringNonNullable($value, $class);
    }

    /**
     * @template T of object
     *
     * @phpstan-assert class-string<T>[] $value
     *
     * @param class-string<T> $class
     *
     * @return class-string<T>[]
     */
    public static function interfaceClassStringArrayNonNullable(mixed $value, string $class): array
    {
        return array_map(
            static fn (string $val) => self::interfaceClassStringNonNullable($val, $class),
            self::nonEmptyStringArrayNonNullable($value),
        );
    }

    /**
     * @template T of object
     *
     * @phpstan-assert ?class-string<T>[] $value
     *
     * @param class-string<T> $class
     *
     * @return ?class-string<T>[]
     */
    public static function interfaceClassStringArrayNullable(mixed $value, string $class): ?array
    {
        if ($value === null) {
            return $value;
        }

        return self::interfaceClassStringArrayNonNullable($value, $class);
    }

    /**
     * @param object|class-string $class
     */
    public static function existingClassAttribute(object|string $class, string $attribute): void
    {
        if (!Attribute::existsOnClass($attribute, $class)) {
            throw new AssertionFailedException(sprintf(
                'Expected instance of class "%s" to be tagged with attribute "%s".',
                Str::displayType($class),
                $attribute,
            ));
        }
    }
}
