<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy;

use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Tool\Str;

final class SnakeCase implements FileNameStrategy
{
    public function getName(string $name): string
    {
        return Str::toSnake($name);
    }
}
