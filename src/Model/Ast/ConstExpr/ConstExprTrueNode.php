<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Override;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode as PHPStanConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use Stringable;

/**
 * @internal
 */
final class ConstExprTrueNode implements Node, Stringable
{
    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    #[Override]
    public function toString(): string
    {
        return TsProperty::TYPE_TRUE;
    }

    #[Override]
    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstExprTrueNode::class);

        return new self();
    }
}
