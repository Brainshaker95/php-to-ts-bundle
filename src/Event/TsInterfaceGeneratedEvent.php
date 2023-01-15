<?php

namespace Brainshaker95\PhpToTsBundle\Event;

use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use PhpParser\Node\Stmt\Class_;
use Symfony\Contracts\EventDispatcher\Event;

class TsInterfaceGeneratedEvent extends Event
{
    public function __construct(
        public ?TsInterface $tsInterface,
        public readonly Class_ $classNode,
    ) {
    }
}
