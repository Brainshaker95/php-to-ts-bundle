<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Exception\UnsupportedNodeException;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode as PHPStanGenericTypeNode;

use function array_flip;
use function array_key_exists;
use function array_map;
use function count;
use function current;
use function implode;
use function sprintf;

/**
 * @internal
 */
final class GenericTypeNode implements Node
{
    /**
     * @param Node[] $genericTypes
     */
    public function __construct(
        public readonly IdentifierTypeNode $type,
        public readonly array $genericTypes,
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $type = $this->type->name;

        if (array_key_exists($type, array_flip(Converter::SIMPLE_TYPES))) {
            return $type;
        }

        $genericTypeCount = count($this->genericTypes);

        if ($type === Converter::TYPE_KEY_OF) {
            if ($genericTypeCount !== 1) {
                throw new UnsupportedNodeException(sprintf(
                    'Expected generic key-of node to contain 1 sub type, %d given.',
                    $genericTypeCount,
                ));
            }

            return 'keyof ' . current($this->genericTypes);
        }

        if ($type === Converter::TYPE_VALUE_OF) {
            if ($genericTypeCount !== 1) {
                throw new UnsupportedNodeException(sprintf(
                    'Expected generic value-of node to contain 1 sub type, %d given.',
                    $genericTypeCount,
                ));
            }

            return 'ValueOf<' . current($this->genericTypes) . '>';
        }

        if ($type === TsProperty::TYPE_UNKNOWN . '[]') {
            $type = 'Array';

            if ($genericTypeCount === 2) {
                $type = 'Record';
            } elseif ($genericTypeCount !== 1) {
                throw new UnsupportedNodeException(sprintf(
                    'Expected generic array node to contain 1 or 2 sub types, %d given.',
                    $genericTypeCount,
                ));
            }
        }

        return $type . '<' . implode(', ', $this->genericTypes) . '>';
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanGenericTypeNode::class);

        foreach ($node->variances as $variance) {
            if ($variance !== PHPStanGenericTypeNode::VARIANCE_INVARIANT) {
                throw new UnsupportedNodeException(sprintf(
                    'Invalid variance "%s" of generic type node. Only %s nodes are supported.',
                    $variance,
                    PHPStanGenericTypeNode::VARIANCE_INVARIANT,
                ));
            }
        }

        $type = PhpStan::toNode($node->type);

        Assert::instanceOf($type, IdentifierTypeNode::class);

        return new self(
            type: $type,
            genericTypes: array_map(PhpStan::toNode(...), $node->genericTypes),
        );
    }
}
