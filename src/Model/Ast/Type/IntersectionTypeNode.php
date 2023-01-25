<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode as PHPStanIntersectionTypeNode;

class IntersectionTypeNode implements Node
{
    /**
     * @param Node[] $types
     */
    public function __construct(
        public readonly array $types,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return '(' . implode(' & ', $this->types) . ')';
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanIntersectionTypeNode::class);

        return new self(
            types: array_map(
                [Type::class, 'fromPhpStan'],
                $node->types,
            ),
        );
    }
}
