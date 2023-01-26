<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode as PHPStanConstTypeNode;

/**
 * @internal
 */
class ConstTypeNode implements Node
{
    public function __construct(
        public readonly Node $constExpr,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->constExpr->toString();
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstTypeNode::class);

        return new self(
            constExpr: PhpStan::toNode($node->constExpr),
        );
    }
}
