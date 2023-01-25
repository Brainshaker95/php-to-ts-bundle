<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
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

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstExprTrueNode::class);

        return new self();
    }
}
