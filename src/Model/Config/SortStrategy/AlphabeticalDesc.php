<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy;

use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Override;

use function strnatcasecmp;

final class AlphabeticalDesc implements SortStrategy
{
    #[Override]
    public function sort(TsProperty $property1, TsProperty $property2): int
    {
        return strnatcasecmp($property2->name, $property1->name);
    }
}
