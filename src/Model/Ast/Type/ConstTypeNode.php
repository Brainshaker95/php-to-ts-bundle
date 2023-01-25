<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Type;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode as PHPStanConstTypeNode;

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

    /**
     * @param PHPStanConstTypeNode $node
     */
    public static function fromPhpStan(PHPStanNode $node): self
    {
        return new self(
            constExpr: Type::fromPhpStan($node->constExpr),
        );
    }
}
