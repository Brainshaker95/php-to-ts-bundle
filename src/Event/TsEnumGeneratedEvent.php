<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Event;

use Brainshaker95\PhpToTsBundle\Model\TsEnum;
use PhpParser\Node\Stmt\Enum_;
use Symfony\Contracts\EventDispatcher\Event;

final class TsEnumGeneratedEvent extends Event
{
    public function __construct(
        public ?TsEnum $tsEnum,
        public readonly Enum_ $enumNode,
    ) {}
}
