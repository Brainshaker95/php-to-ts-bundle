<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\Quotable;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasQuotes;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode as PHPStanUnionTypeNode;

use function array_map;
use function implode;

/**
 * @internal
 */
final class UnionTypeNode implements Node, Quotable
{
    use HasQuotes;

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
        if ($this->quotes) {
            foreach ($this->types as $type) {
                if ($type instanceof Quotable) {
                    $type->setQuotes($this->quotes);
                }
            }
        }

        return '(' . implode(' | ', $this->types) . ')';
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanUnionTypeNode::class);

        return new self(
            types: array_map(
                [PhpStan::class, 'toNode'],
                $node->types,
            ),
        );
    }
}
