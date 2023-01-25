<?php

namespace Brainshaker95\PhpToTsBundle\Model\Ast\Type;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Tool\Type;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode as PHPStanArrayTypeNode;

class ArrayTypeNode implements Node
{
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
        return $this->type . '[]';
    }

    /**
     * @param PHPStanArrayTypeNode $node
     */
    public static function fromPhpStan(PHPStanNode $node): self
    {
        return new self(
            type: Type::fromPhpStan($node->type),
        );
    }
}
