<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode as PHPStanConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

/**
 * @internal
 */
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

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstExprFalseNode::class);

        return new self();
    }
}
