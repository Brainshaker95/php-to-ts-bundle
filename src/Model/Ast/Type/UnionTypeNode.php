<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Type;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode as PHPStanUnionTypeNode;

class UnionTypeNode implements Node
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
        return '(' . implode(' | ', $this->types) . ')';
    }

    /**
     * @param PHPStanUnionTypeNode $node
     */
    public static function fromPhpStan(PHPStanNode $node): self
    {
        return new self(
            types: array_map(
                [Type::class, 'fromPhpStan'],
                $node->types,
            ),
        );
    }
}
