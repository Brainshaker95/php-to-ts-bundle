<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Interface;

use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use Stringable;

/**
 * @internal
 */
interface Node extends Stringable
{
    public function toString(): string;

    public static function fromPhpStan(PHPStanNode $node): self;
}
