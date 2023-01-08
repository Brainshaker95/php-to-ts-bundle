<?php

namespace Brainshaker95\PhpToTsBundle\Tool;

use function Symfony\Component\String\u;

abstract class Str
{
    public static function toKebabCase(string $string): string
    {
        return u($string)
            ->snake()
            ->replace('_', '-')
            ->toString()
        ;
    }
}
