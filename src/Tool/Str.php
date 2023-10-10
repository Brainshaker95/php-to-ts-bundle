<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use Symfony\Component\String\UnicodeString;

use function array_filter;
use function array_map;
use function count;
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

        $lines     = array_filter($string->split("\n"), static fn (UnicodeString $line) => $line->length());
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
}
