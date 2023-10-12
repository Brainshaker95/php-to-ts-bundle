<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Event;

use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Property;
use Symfony\Contracts\EventDispatcher\Event;

final class TsPropertyGeneratedEvent extends Event
{
    public function __construct(
        public ?TsProperty $tsProperty,
        public readonly Param|Property|EnumCase $propertyNode,
    ) {}
}
