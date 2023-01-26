<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Error;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode as PHPStanConstFetchNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

/**
 * @internal
 */
class ConstFetchNode implements Node
{
    public function __construct(
        public readonly string $className,
        public readonly string $name,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        if ($this->className === '') {
            return TsProperty::TYPE_UNKNOWN;
        }

        try {
            $value = constant($this->className . '::' . $this->name);
        } catch (Error) {
            return TsProperty::TYPE_UNKNOWN;
        }

        if (is_string($value)) {
            // TODO: config for quote style ' or "
            return '\'' . $value . '\'';
        }

        return TsProperty::TYPE_UNKNOWN . (is_array($value) ? '[]' : '');
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstFetchNode::class);

        return new self(
            className: $node->className,
            name: $node->name,
        );
    }
}
