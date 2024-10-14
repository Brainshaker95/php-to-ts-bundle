<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use Override;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode as PHPStanConstTypeNode;
use Stringable;

/**
 * @internal
 */
final class ConstTypeNode implements Node, Stringable
{
    public function __construct(
        public readonly Node $constExpr,
    ) {}

    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    #[Override]
    public function toString(): string
    {
        return $this->constExpr->toString();
    }

    #[Override]
    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstTypeNode::class);

        return new self(
            constExpr: PhpStan::toNode($node->constExpr),
        );
    }
}
