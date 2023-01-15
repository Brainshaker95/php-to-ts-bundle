<?php

namespace Brainshaker95\PhpToTsBundle\Event;

use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Symfony\Contracts\EventDispatcher\Event;

class TsPropertyGeneratedEvent extends Event
{
    public function __construct(
        public ?TsProperty $tsProperty,
        public readonly Param|Property $propertyNode,
    ) {
    }
}
