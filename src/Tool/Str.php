<?php

namespace Brainshaker95\PhpToTsBundle\Tool;

use function Symfony\Component\String\u;

abstract class Str
{
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
}
