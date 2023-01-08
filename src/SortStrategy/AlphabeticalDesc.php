<?php

namespace Brainshaker95\PhpToTsBundle\SortStrategy;

use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;

class AlphabeticalDesc implements SortStrategy
{
    public function sort(TsProperty $property1, TsProperty $property2): int
    {
        return $property2->name <=> $property1->name;
    }
}
