<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Exception\UnsupportedNodeException;
use Brainshaker95\PhpToTsBundle\Interface\Indentable;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\Quotable;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasIndent;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasQuotes;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode as PHPStanArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode as PHPStanArrayShapeNode;

use const PHP_EOL;

use function array_map;
use function array_unshift;
use function implode;
use function sprintf;

/**
 * @internal
 */
final class ArrayShapeNode implements Indentable, Node, Quotable
{
    use HasIndent;
    use HasQuotes;

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
        $items = implode(PHP_EOL, $this->items);

        if (!$items) {
            return TsProperty::TYPE_UNKNOWN . '[]';
        }

        $hasKeys        = self::hasKeys($this->items);
        $openingBracket = $hasKeys ? '{' : '[';
        $closingBracket = $hasKeys ? '}' : ']';

        return $openingBracket . PHP_EOL
            . $items . PHP_EOL
            . ($this->indent?->toString() ?? '') . $closingBracket;
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanArrayShapeNode::class);

        $items = array_map(
            [ArrayShapeItemNode::class, 'fromPhpStan'],
            $node->items,
        );

        if (!$node->sealed) {
            $hasKeys      = self::hasKeys($items);
            $unsealedNode = ArrayShapeItemNode::createUnsealedItem($hasKeys);

            if ($hasKeys) {
                array_unshift($items, $unsealedNode);
            } else {
                $items[] = $unsealedNode;
            }
        }

        return new self(
            items: $items,
        );
    }

    /**
     * @param array<ArrayShapeItemNode|PHPStanArrayShapeItemNode> $items
     */
    private static function hasKeys(array $items): bool
    {
        $hasKeys = false;

        foreach ($items as $item) {
            if (($item instanceof ArrayShapeItemNode && $item->keyNode)
                || ($item instanceof PHPStanArrayShapeItemNode && $item->keyName)) {
                $hasKeys = true;
            } elseif ($hasKeys) {
                throw new UnsupportedNodeException(sprintf(
                    'Invalid item "%s". All array shape properties either have to have keys or not. Mixing is not allowed.',
                    $item->__toString(),
                ));
            }
        }

        return $hasKeys;
    }
}
