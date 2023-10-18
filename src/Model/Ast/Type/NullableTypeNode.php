<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode as PHPStanNullableTypeNode;

/**
 * @internal
 */
final class NullableTypeNode implements Node
{
    public function __construct(
        public readonly Node $type,
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return '(' . $this->type . ' | ' . TsProperty::TYPE_NULL . ')';
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanNullableTypeNode::class);

        return new self(
            type: PhpStan::toNode($node->type),
        );
    }
}
