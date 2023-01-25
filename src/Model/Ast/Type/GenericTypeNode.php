<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Exception\AssertionFailedException;
use Brainshaker95\PhpToTsBundle\Exception\UnsupportedNodeException;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode as PHPStanGenericTypeNode;

class GenericTypeNode implements Node
{
    /**
     * @param Node[] $genericTypes
     */
    public function __construct(
        public readonly IdentifierTypeNode $type,
        public readonly array $genericTypes,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $type = $this->type->name;

        if ($type === 'Array') {
            $genericTypeCount = count($this->genericTypes);

            if ($genericTypeCount === 2) {
                $type = 'Record';
            } elseif ($genericTypeCount !== 1) {
                throw new UnsupportedNodeException(sprintf(
                    'Expected generic array node to contain 1 or 2 subtypes, %s given.',
                    $genericTypeCount,
                ));
            }
        } elseif (array_key_exists($type, array_flip(Converter::NON_ITERABLE_TYPE_MAP))) {
            return $type;
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

        if (!$type instanceof IdentifierTypeNode) {
            throw new AssertionFailedException(sprintf(
                'Expected node to be an instance of "%s", "%s" given.',
                IdentifierTypeNode::class,
                get_class($type),
            ));
        }

        return new self(
            type: $type,
            genericTypes: array_map(
                [PhpStan::class, 'toNode'],
                $node->genericTypes,
            ),
        );
    }
}
