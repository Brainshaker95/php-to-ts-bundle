<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode as PHPStanIdentifierTypeNode;

use function array_key_exists;
use function in_array;

/**
 * @internal
 */
final class IdentifierTypeNode implements Node
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->name;
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanIdentifierTypeNode::class);

        $name = $node->name;

        if (array_key_exists($name, Converter::NON_ITERABLE_TYPE_MAP)) {
            $name = Converter::NON_ITERABLE_TYPE_MAP[$name];
        } elseif (in_array($name, Converter::ITERABLE_TYPES, true)) {
            $name = TsProperty::TYPE_UNKNOWN . '[]';
        }

        return new self(
            name: $name,
        );
    }
}
