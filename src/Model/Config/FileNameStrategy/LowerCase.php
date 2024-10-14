<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy;

use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Override;

final class LowerCase implements FileNameStrategy
{
    #[Override]
    public function getName(string $name): string
    {
        return Str::toLower($name);
    }
}
