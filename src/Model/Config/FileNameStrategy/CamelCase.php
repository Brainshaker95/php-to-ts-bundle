<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy;

use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Tool\Str;

final class CamelCase implements FileNameStrategy
{
    public function getName(string $name): string
    {
        return Str::toCamel($name);
    }
}
