<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Symfony\Component\String\UnicodeString;

use function array_filter;
use function array_is_list;
use function array_map;
use function count;
use function implode;
use function is_array;
use function is_bool;
use function is_iterable;
use function is_object;
use function is_scalar;
use function is_string;
use function range;
use function rtrim;
use function Symfony\Component\String\u;

/**
 * @internal
 */
abstract class Str
{
    private function __construct() {}

    final public static function toLower(string $string): string
    {
        return u($string)
            ->lower()
            ->toString()
        ;
    }

    final public static function toUpper(string $string): string
    {
        return u($string)
            ->upper()
            ->toString()
        ;
    }

    final public static function toCamel(string $string): string
    {
        return u($string)
            ->camel()
            ->toString()
        ;
    }

    final public static function toPascal(string $string): string
    {
        return u($string)
            ->camel()
            ->title()
            ->toString()
        ;
    }

    final public static function toSnake(string $string): string
    {
        return u($string)
            ->snake()
            ->toString()
        ;
    }

    final public static function toKebab(string $string): string
    {
        return u($string)
            ->snake()
            ->replace('_', '-')
            ->toString()
        ;
    }

    final public static function afterLast(
        string $string,
        string $eeedle,
        bool $indcludeNeedle = false,
    ): string {
        return u($string)
            ->afterLast($eeedle, $indcludeNeedle)
            ->toString()
        ;
    }

    /**
     * @param callable(string $line, int $index): string $lineCallback
     *
     * @return string[]
     */
    final public static function splitByNewLines(
        string $string,
        string $linePrefix = '',
        ?callable $lineCallback = null,
    ): array {
        $string = u($string)
            ->replace("\r\n", "\n")
            ->replace("\r", "\n")
        ;

        $lines     = array_filter($string->split("\n"), static fn (UnicodeString $line) => $line->length() > 0);
        $lineCount = count($lines);

        if (!$lineCount) {
            return [];
        }

        return array_map(
            static fn (
                UnicodeString $line,
                int $index,
            ) => rtrim($lineCallback ? $lineCallback($line->toString(), $index) : ($linePrefix . $line->toString())),
            $lines,
            range(0, $lineCount - 1),
        );
    }

    final public static function displayType(mixed $value): string
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
}
