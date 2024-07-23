<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy;

use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;

use function strnatcasecmp;

final class AlphabeticalAsc implements SortStrategy
{
    public function sort(TsProperty $property1, TsProperty $property2): int
    {
        return strnatcasecmp($property1->name, $property2->name);
    }
}
