<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\QuotesAware;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasQuotes;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode as PHPStanConstExprStringNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

/**
 * @internal
 */
final class ConstExprStringNode implements Node, QuotesAware
{
    use HasQuotes;

    public function __construct(
        private readonly string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(bool $quoted = true): string
    {
        if (!$quoted) {
            return $this->value;
        }

        return $this->quotes
            ? $this->quotes->toString($this->value)
            : Quotes::default($this->value);
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstExprStringNode::class);

        return new self(
            value: $node->value,
        );
    }
}
