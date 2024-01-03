<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNullNode as PHPStanConstExprNullNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

/**
 * @internal
 */
final class ConstExprNullNode implements Node
{
    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return TsProperty::TYPE_NULL;
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstExprNullNode::class);

        return new self();
    }
}
