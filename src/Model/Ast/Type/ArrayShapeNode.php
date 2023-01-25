<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode as PHPStanArrayShapeNode;

class ArrayShapeNode implements Node
{
    private ?Indent $indent = null;

    /**
     * @param ArrayShapeItemNode[] $items
     */
    public function __construct(
        public readonly array $items,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $hasKeys        = (bool) current(array_filter($this->items, fn (ArrayShapeItemNode $node) => $node->keyNode));
        $openingBracket = $hasKeys ? '{' : '[';
        $closingBracket = $hasKeys ? '}' : ']';

        return $openingBracket . PHP_EOL
            . implode('', $this->items)
            . ($this->indent?->toString() ?? '') . $closingBracket;
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanArrayShapeNode::class);

        return new self(
            items: array_map(
                [ArrayShapeItemNode::class, 'fromPhpStan'],
                $node->items,
            ),
        );
    }

    public function setIndent(?Indent $indent): self
    {
        $this->indent = $indent;

        return $this;
    }
}
