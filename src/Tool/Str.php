<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use function array_filter;
use function array_map;
use function count;
use function preg_split;
use function range;
use function rtrim;
use function Symfony\Component\String\u;

/**
 * @internal
 */
abstract class Str
{
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
        $lines = array_filter(preg_split('/\n/', $string) ?: []);

        if (empty($lines)) {
            return [];
        }

        return array_map(
            static fn (
                string $line,
                int $index,
            ) => rtrim($lineCallback ? $lineCallback($line, $index) : ($linePrefix . $line)),
            $lines,
            range(0, count($lines) - 1),
        );
    }
}
