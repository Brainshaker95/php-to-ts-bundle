<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode as PHPStanArrayShapeItemNode;

class ArrayShapeItemNode implements Node
{
    private ?Indent $indent = null;

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

        $key = (string) $this->keyNode;

        return sprintf(
            '%s%s%s: %s;' . PHP_EOL,
            $this->indent?->toString() ?? '',
            // TODO: config for quote style ' or "
            is_numeric($key[0]) ? '\'' . $key . '\'' : $key,
            $this->isOptional ? '?' : '',
            (string) $this->valueNode,
        );
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanArrayShapeItemNode::class);

        return new self(
            keyNode: $node->keyName ? Type::fromPhpStan($node->keyName) : null,
            isOptional: $node->optional,
            valueNode: Type::fromPhpStan($node->valueType),
        );
    }

    public function setIndent(?Indent $indent): self
    {
        $this->indent = $indent;

        return $this;
    }
}
