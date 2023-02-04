<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Indentable;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\Quotable;
use Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr\ConstExprStringNode;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasIndent;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasQuotes;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode as PHPStanArrayShapeItemNode;

use const PHP_EOL;

use function is_numeric;
use function sprintf;

/**
 * @internal
 */
final class ArrayShapeItemNode implements Indentable, Node, Quotable
{
    use HasIndent;
    use HasQuotes;

    public function __construct(
        public readonly ?Node $keyNode,
        public readonly bool $isOptional,
        public readonly Node $valueNode,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        if (!$this->keyNode) {
            return sprintf(
                '%s%s,' . PHP_EOL,
                $this->indent?->toString() ?? '',
                (string) $this->valueNode,
            );
        }

        $key = $this->keyNode instanceof ConstExprStringNode
            ? $this->keyNode->toString(false)
            : (string) $this->keyNode;

        if (is_numeric($key[0])) {
            $key = $this->quotes
                ? $this->quotes->toString($key)
                : Quotes::default($key);
        }

        return sprintf(
            '%s%s%s: %s;' . PHP_EOL,
            $this->indent?->toString() ?? '',
            $key,
            $this->isOptional ? '?' : '',
            (string) $this->valueNode,
        );
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanArrayShapeItemNode::class);

        return new self(
            keyNode: $node->keyName ? PhpStan::toNode($node->keyName) : null,
            isOptional: $node->optional,
            valueNode: PhpStan::toNode($node->valueType),
        );
    }
}
