<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode as PHPStanConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

class ConstExprTrueNode implements Node
{
    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return 'true';
    }

    /**
     * @param PHPStanConstExprTrueNode $node
     */
    public static function fromPhpStan(PHPStanNode $node): self
    {
        return new self();
    }
}
