<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Symfony\Component\String\UnicodeString;

use const PHP_EOL;

use function array_is_list;
use function array_map;
use function implode;
use function is_array;
use function is_bool;
use function is_iterable;
use function is_object;
use function is_scalar;
use function is_string;
use function str_contains;
use function Symfony\Component\String\u;

/**
 * @internal
 */
final class Str
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct() {}

    public static function toLower(string $string): string
    {
        return u($string)
            ->lower()
            ->toString()
        ;
    }

    public static function toUpper(string $string): string
    {
        return u($string)
            ->upper()
            ->toString()
        ;
    }

    public static function toCamel(string $string): string
    {
        return u($string)
            ->camel()
            ->toString()
        ;
    }

    public static function toPascal(string $string): string
    {
        return u($string)
            ->camel()
            ->title()
            ->toString()
        ;
    }

    public static function toSnake(string $string): string
    {
        return u($string)
            ->snake()
            ->toString()
        ;
    }

    public static function toKebab(string $string): string
    {
        return u($string)
            ->snake()
            ->replace('_', '-')
            ->toString()
        ;
    }

    /**
     * @phpstan-assert-if-true !non-empty-string $string
     */
    public static function isEmpty(?string $string): bool
    {
        return $string === null || u($string)->trim()->length() === 0;
    }

    public static function trimEnd(
        string $string,
        string $additionalChars = '',
        string $chars = " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}",
    ): string {
        return u($string)
            ->trimEnd($chars . $additionalChars)
            ->toString()
        ;
    }

    public static function indentAndPrefixLines(string $string, ?Indent $indent, string $prefix = ''): string
    {
        $lines = u($string)
            ->replace("\r\n", "\n")
            ->replace("\r", "\n")
            ->split("\n")
        ;

        return implode(PHP_EOL, array_map(
            static fn (UnicodeString $line): string => $line->toString() === ''
                ? $indent?->toString() . self::trimEnd($prefix) . $line
                : $indent?->toString() . $prefix . $line,
            $lines,
        ));
    }

    public static function containsNewlines(string $string): bool
    {
        return u($string)
            ->replace("\r\n", "\n")
            ->replace("\r", "\n")
            ->indexOf("\n") !== null
        ;
    }

    public static function displayType(mixed $value): string
    {
        if ($value === null) {
            return Converter::TYPE_NULL;
        }

        if (is_string($value)) {
            return '"' . $value . '"';
        }

        if (is_bool($value)) {
            return $value ? Converter::TYPE_TRUE : Converter::TYPE_FALSE;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_iterable($value)) {
            $hasKeys = is_array($value) ? !array_is_list($value) : false;
            $values  = [];

            foreach ($value as $key => $item) {
                if ($hasKeys) {
                    $values[] = $key . ': ' . self::displayType($item);
                } else {
                    $values[] = self::displayType($item);
                }
            }

            return Converter::TYPE_ARRAY . '{' . implode(', ', $values) . '}';
        }

        if (is_object($value)) {
            return $value::class;
        }

        return Converter::TYPE_MIXED;
    }

    public static function getShortClassName(string $className): string
    {
        return str_contains($className, '\\')
            ? u($className)->afterLast('\\')->toString()
            : $className;
    }

    public static function getFirstNamespacePart(string $className): ?string
    {
        return str_contains($className, '\\')
            ? u($className)->before('\\')->toString()
            : null;
    }

    public static function getTrailingNamespaceParts(string $className): ?string
    {
        return str_contains($className, '\\')
            ? u($className)->after('\\')->toString()
            : null;
    }
}
