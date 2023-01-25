<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode as PHPStanConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

class ConstExprIntegerNode implements Node
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
     * @param PHPStanConstExprIntegerNode $node
     */
    public static function fromPhpStan(PHPStanNode $node): self
    {
        return new self(
            value: $node->value,
        );
    }
}
