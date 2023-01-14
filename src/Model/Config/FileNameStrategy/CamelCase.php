<?php

namespace Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy;

use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Tool\Str;

class CamelCase implements FileNameStrategy
{
    public function getName(string $name): string
    {
        return Str::toCamel($name);
    }
}
