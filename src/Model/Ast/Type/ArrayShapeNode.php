<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Indentable;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\Quotable;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasIndent;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasQuotes;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode as PHPStanArrayShapeNode;

use const PHP_EOL;

use function array_filter;
use function array_map;
use function current;
use function implode;

/**
 * @internal
 */
final class ArrayShapeNode implements Indentable, Node, Quotable
{
    use HasIndent;
    use HasQuotes;

    /**
     * @param ArrayShapeItemNode[] $items
     * @phpstan-param PHPStanArrayShapeNode::KIND_* $kind
     */
    public function __construct(
        public readonly array $items,
        public readonly string $kind,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $isList         = $this->kind === PHPStanArrayShapeNode::KIND_LIST;
        $openingBracket = '[';
        $closingBracket = ']';

        if (!$isList) {
            $hasKeys        = (bool) current(array_filter($this->items, static fn (ArrayShapeItemNode $node) => $node->keyNode));
            $openingBracket = $hasKeys ? '{' : '[';
            $closingBracket = $hasKeys ? '}' : ']';
        }

        return $openingBracket . PHP_EOL
            . implode(PHP_EOL, $this->items) . PHP_EOL
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
            kind: $node->kind,
        );
    }
}
