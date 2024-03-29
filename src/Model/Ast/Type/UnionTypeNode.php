<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode as PHPStanUnionTypeNode;

use function array_map;
use function implode;

/**
 * @internal
 */
final class UnionTypeNode implements Node
{
    /**
     * @param Node[] $types
     */
    public function __construct(
        public readonly array $types,
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return '(' . implode(' | ', $this->types) . ')';
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanUnionTypeNode::class);

        return new self(
            types: array_map(PhpStan::toNode(...), $node->types),
        );
    }
}
