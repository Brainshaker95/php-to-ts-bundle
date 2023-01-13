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

    public static function toKebab(string $string): string
    {
        return u($string)
            ->snake()
            ->replace('_', '-')
            ->toString()
        ;
    }
}
