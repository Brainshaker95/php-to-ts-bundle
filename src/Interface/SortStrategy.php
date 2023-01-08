<?php

namespace Brainshaker95\PhpToTsBundle\Interface;

use Brainshaker95\PhpToTsBundle\Model\TsProperty;

interface SortStrategy
{
    public function sort(TsProperty $property1, TsProperty $property2): int;
}
