<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Indentable;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\Quotable;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasIndent;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasQuotes;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\PhpStan;
use Error;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode as PHPStanConstFetchNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

use function constant;

/**
 * @internal
 */
final class ConstFetchNode implements Indentable, Node, Quotable
{
    use HasIndent;
    use HasQuotes;

    public function __construct(
        public readonly string $className,
        public readonly string $name,
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        try {
            $value = $this->className
                ? constant($this->className . '::' . $this->name)
                : constant($this->name);
        } catch (Error) {
            return TsProperty::TYPE_UNKNOWN;
        }

        return PhpStan::phpValueToTsType(
            $value,
            $this->indent ?? new Indent(),
            $this->quotes ?? new Quotes(),
        );
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
