<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Event;

use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use PhpParser\Node\Stmt\Class_;
use Symfony\Contracts\EventDispatcher\Event;

final class TsInterfaceGeneratedEvent extends Event
{
    public function __construct(
        public ?TsInterface $tsInterface,
        public readonly Class_ $classNode,
    ) {
    }
}
