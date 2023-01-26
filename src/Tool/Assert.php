<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Brainshaker95\PhpToTsBundle\Exception\AssertionFailedException;
use ReflectionClass;
use Stringable;

/**
 * @internal
 */
abstract class Assert
{
    /**
     * @phpstan-assert non-empty-string $value
     *
     * @return non-empty-string
     */
    public static function nonEmptyStringNonNullable(mixed $value): string
    {
        if (!is_string($value) || !$value) {
            throw new AssertionFailedException(sprintf(
                'Expected value "%s" to be a non empty string.',
                self::mixedToString($value),
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
        if (is_null($value)) {
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
        $intval = intval($value);

        if (filter_var($value, FILTER_VALIDATE_INT) === false || $intval < 0) {
            throw new AssertionFailedException(sprintf(
                'Expected value "%s" to be a non negative integer.',
                self::mixedToString($value),
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
        if (is_null($value)) {
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
            || !empty(array_filter($value, fn (mixed $v) => !is_string($v) || (is_string($v) && !$v)))) {
            throw new AssertionFailedException(sprintf(
                'Expected value "%s" to be a non empty string array.',
                self::mixedToString($value),
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
        if (is_null($value)) {
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
        if (!is_string($value) || !in_array($value, $allowedStrings)) {
            throw new AssertionFailedException(sprintf(
                'Expected value "%s" to be contained in array "%s".',
                self::mixedToString($value),
                self::mixedToString($allowedStrings),
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
        if (is_null($value)) {
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
                'Expected object with class "%s" to be an instance of class "%s".',
                get_class($value),
                $class,
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
                'Expected value "%s" to be a class string of a class that implements "%s".',
                self::mixedToString($value),
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
        if (is_null($value)) {
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
            fn (string $v) => self::interfaceClassStringNonNullable($v, $class),
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
        if (is_null($value)) {
            return $value;
        }

        return self::interfaceClassStringArrayNonNullable($value, $class);
    }

    private static function mixedToString(mixed $value): string
    {
        if ($value instanceof Stringable) {
            return $value->__toString();
        }

        if ($value === null) {
            return '<null>';
        }

        if (is_scalar($value)) {
            return strval($value);
        }

        if (is_iterable($value)) {
            $value = !is_array($value) ? iterator_to_array($value) : $value;

            return '[' . implode(', ', $value) . ']';
        }

        return '';
    }
}
