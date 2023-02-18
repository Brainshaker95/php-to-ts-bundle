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
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function is_iterable;
use function is_numeric;
use function is_object;
use function is_scalar;
use function is_string;
use function iterator_to_array;
use function sprintf;

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
    final public static function nonEmptyStringNonNullable(mixed $value): string
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
    final public static function nonEmptyStringNullable(mixed $value): ?string
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
    final public static function nonNegativeIntegerNonNullable(mixed $value): int
    {
        $intval = is_numeric($value) ? (int) $value : -1;

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
    final public static function nonNegativeIntegerNullable(mixed $value): ?int
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
    final public static function nonEmptyStringArrayNonNullable(mixed $value): array
    {
        if (!is_array($value)
            || count(array_filter($value, static fn (mixed $val) => !is_string($val) || !$val))) {
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
    final public static function nonEmptyStringArrayNullable(mixed $value): ?array
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
    final public static function inStringArrayNonNullable(mixed $value, array $allowedStrings): string
    {
        if (!is_string($value) || !in_array($value, $allowedStrings, true)) {
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
    final public static function inStringArrayNullable(mixed $value, array $allowedStrings): ?string
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
    final public static function instanceOf(object $value, string $class): object
    {
        if (!$value instanceof $class) {
            throw new AssertionFailedException(sprintf(
                'Expected object to be an instance of "%s", "%s" given.',
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
    final public static function interfaceClassStringNonNullable(mixed $value, string $class): string
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
    final public static function interfaceClassStringNullable(mixed $value, string $class): ?string
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
    final public static function interfaceClassStringArrayNonNullable(mixed $value, string $class): array
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
    final public static function interfaceClassStringArrayNullable(mixed $value, string $class): ?array
    {
        if ($value === null) {
            return $value;
        }

        return self::interfaceClassStringArrayNonNullable($value, $class);
    }

    final public static function existingAttribute(mixed $value, string $attribute): void
    {
        if ((!is_string($value) && !is_object($value)) || !Attribute::exists($value, $attribute)) {
            throw new AssertionFailedException(sprintf(
                'Expected value for parameter "typeScriptable" to be an instance of a class tagged with the "%s" attribute, "%s" given.',
                $attribute,
                self::mixedToString($value),
            ));
        }
    }

    private static function mixedToString(mixed $value): string
    {
        if ($value === null) {
            return '<null>';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_object($value)) {
            return $value::class;
        }

        if (is_iterable($value)) {
            /**
             * Call to function is_array() with array will always evaluate to true.
             * This is not the case since $value could also be a Traversable.
             *
             * @phpstan-ignore-next-line
             */
            $value = !is_array($value) ? iterator_to_array($value) : $value;

            return '[' . implode(', ', $value) . ']';
        }

        return '';
    }
}
