<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy;

use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;

final class ReadonlyFirst implements SortStrategy
{
    public function sort(TsProperty $property1, TsProperty $property2): int
    {
        return $property2->isReadonly <=> $property1->isReadonly;
    }
}
