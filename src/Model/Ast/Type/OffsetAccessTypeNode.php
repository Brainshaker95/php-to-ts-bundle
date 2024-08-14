<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\OffsetAccessTypeNode as PHPStanOffsetAccessTypeNode;
use Stringable;

/**
 * @internal
 */
final class OffsetAccessTypeNode implements Node, Stringable
{
    public function __construct(
        public readonly Node $type,
        public readonly Node $offset,
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->type . '[' . $this->offset . ']';
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanOffsetAccessTypeNode::class);

        return new self(
            type: PhpStan::toNode($node->type),
            offset: PhpStan::toNode($node->offset),
        );
    }
}
