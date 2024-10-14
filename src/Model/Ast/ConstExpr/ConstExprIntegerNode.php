<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Override;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode as PHPStanConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use Stringable;

/**
 * @internal
 */
final class ConstExprIntegerNode implements Node, Stringable
{
    public function __construct(
        private readonly string $value,
    ) {}

    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    #[Override]
    public function toString(): string
    {
        return $this->value;
    }

    #[Override]
    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstExprIntegerNode::class);

        return new self(
            value: $node->value,
        );
    }
}
