<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode as PHPStanConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use Stringable;

/**
 * @internal
 */
final class ConstExprFalseNode implements Node, Stringable
{
    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return TsProperty::TYPE_FALSE;
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstExprFalseNode::class);

        return new self();
    }
}
