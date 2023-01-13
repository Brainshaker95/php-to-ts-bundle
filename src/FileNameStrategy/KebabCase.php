<?php

namespace Brainshaker95\PhpToTsBundle\FileNameStrategy;

use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Tool\Str;

class KebabCase implements FileNameStrategy
{
    public function getName(string $name): string
    {
        return Str::toKebab($name);
    }
}
