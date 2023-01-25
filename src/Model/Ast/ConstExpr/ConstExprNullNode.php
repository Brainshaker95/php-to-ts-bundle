<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNullNode as PHPStanConstExprNullNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

class ConstExprNullNode implements Node
{
    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return 'null';
    }

    /**
     * @param PHPStanConstExprNullNode $node
     */
    public static function fromPhpStan(PHPStanNode $node): self
    {
        return new self();
    }
}
