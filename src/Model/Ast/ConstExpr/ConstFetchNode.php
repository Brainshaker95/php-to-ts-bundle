<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Ast\ConstExpr;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\Quotable;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasQuotes;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Error;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode as PHPStanConstFetchNode;
use PHPStan\PhpDocParser\Ast\Node as PHPStanNode;

use function array_is_list;
use function constant;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

/**
 * @internal
 */
final class ConstFetchNode implements Node, Quotable
{
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
        if ($this->className === '') {
            return TsProperty::TYPE_UNKNOWN;
        }

        try {
            $value = constant($this->className . '::' . $this->name);
        } catch (Error) {
            return TsProperty::TYPE_UNKNOWN;
        }

        return $this->valueToString($value);
    }

    public static function fromPhpStan(PHPStanNode $node): self
    {
        Assert::instanceOf($node, PHPStanConstFetchNode::class);

        return new self(
            className: $node->className,
            name: $node->name,
        );
    }

    private function valueToString(mixed $value): string
    {
        if ($value === null) {
            return (new ConstExprNullNode())->toString();
        }

        if (is_string($value)) {
            $node = (new ConstExprStringNode($value));

            if ($this->quotes) {
                $node->setQuotes($this->quotes);
            }

            return $node->toString();
        }

        if (is_bool($value)) {
            return $value
                ? (new ConstExprTrueNode())->toString()
                : (new ConstExprFalseNode())->toString();
        }

        if (is_int($value)) {
            return (new ConstExprIntegerNode((string) $value))->toString();
        }

        if (is_float($value)) {
            return (new ConstExprFloatNode((string) $value))->toString();
        }

        if (!is_array($value)) {
            return TsProperty::TYPE_UNKNOWN;
        }

        $hasKeys        = !array_is_list($value);
        $openingBracket = $hasKeys ? '{ ' : '[';
        $closingBracket = $hasKeys ? ' }' : ']';
        $values         = [];

        foreach ($value as $key => $item) {
            try {
                $itemValue = constant($item);
            } catch (Error) {
                $itemValue = $item;
            }

            if ($hasKeys) {
                $values[] = $key . ': ' . $this->valueToString($itemValue);
            } else {
                $values[] = $this->valueToString($itemValue);
            }
        }

        return $openingBracket
            . implode(', ', $values)
            . $closingBracket;
    }
}
