<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Tool;

use function array_filter;
use function array_map;
use function preg_split;
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

    /**
     * @return string[]
     */
    final public static function splitByNewLines(string $string, string $linePrefix = ''): array
    {
        return array_map(
            static fn (string $line) => $linePrefix . $line,
            array_filter(preg_split('/\n/', $string) ?: []),
        );
    }
}
