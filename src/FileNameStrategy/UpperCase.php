<?php

namespace Brainshaker95\PhpToTsBundle\FileNameStrategy;

use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Tool\Str;

class UpperCase implements FileNameStrategy
{
    public function getName(string $name): string
    {
        return Str::toUpper($name);
    }
}
