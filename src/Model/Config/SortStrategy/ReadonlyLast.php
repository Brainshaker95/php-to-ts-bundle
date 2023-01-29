<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy;

use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;

final class ReadonlyLast implements SortStrategy
{
    public function sort(TsProperty $property1, TsProperty $property2): int
    {
        return $property1->isReadonly <=> $property2->isReadonly;
    }
}
