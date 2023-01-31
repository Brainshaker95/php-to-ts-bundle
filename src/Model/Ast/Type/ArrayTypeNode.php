<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\QuotesAware;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasQuotes;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode as PHPStanArrayTypeNode;

/**
 * @internal
 */
final class ArrayTypeNode implements Node, QuotesAware
{
    use HasQuotes;

    public function __construct(
        public readonly Node $type,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        if ($this->type instanceof QuotesAware && $this->quotes) {
            $this->type->setQuotes($this->quotes);
        }

        return $this->type . '[]';
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanArrayTypeNode::class);

        return new self(
            type: PhpStan::toNode($node->type),
        );
    }
}
