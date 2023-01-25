<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode as PHPStanConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

class ConstExprFalseNode implements Node
{
    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return 'false';
    }

    /**
     * @param PHPStanConstExprFalseNode $node
     */
    public static function fromPhpStan(PHPStanNode $node): self
    {
        return new self();
    }
}
