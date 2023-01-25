<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFloatNode as PHPStanConstExprFloatNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

class ConstExprFloatNode implements Node
{
    public function __construct(
        private readonly string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @param PHPStanConstExprFloatNode $node
     */
    public static function fromPhpStan(PHPStanNode $node): self
    {
        return new self(
            value: $node->value,
        );
    }
}
