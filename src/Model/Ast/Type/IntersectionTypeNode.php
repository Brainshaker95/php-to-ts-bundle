<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use Override;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode as PHPStanIntersectionTypeNode;
use Stringable;

use function array_map;
use function implode;

/**
 * @internal
 */
final class IntersectionTypeNode implements Node, Stringable
{
    /**
     * @param Node[] $types
     */
    public function __construct(
        public readonly array $types,
    ) {}

    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    #[Override]
    public function toString(): string
    {
        return '(' . implode(' & ', $this->types) . ')';
    }

    #[Override]
    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanIntersectionTypeNode::class);

        return new self(
            types: array_map(PhpStan::toNode(...), $node->types),
        );
    }
}
