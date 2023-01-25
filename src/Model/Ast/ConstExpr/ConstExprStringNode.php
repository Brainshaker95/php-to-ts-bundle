<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode as PHPStanConstExprStringNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

class ConstExprStringNode implements Node
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
        // TODO: config for quote style ' or "
        return '\'' . $this->value . '\'';
    }

    /**
     * @param PHPStanConstExprStringNode $node
     */
    public static function fromPhpStan(PHPStanNode $node): self
    {
        return new self(
            value: $node->value,
        );
    }
}
