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
use ReflectionClass;
use ReflectionClassConstant;
use Stringable;

use const ARRAY_FILTER_USE_KEY;

use function array_filter;
use function array_keys;
use function array_map;
use function class_exists;
use function constant;
use function fnmatch;
use function implode;
use function Symfony\Component\String\u;

/**
 * @internal
 */
final class ConstFetchNode implements Indentable, Node, Quotable, Stringable
{
    use HasIndent;
    use HasQuotes;

    public function __construct(
        public string $className,
        public readonly string $name,
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $hasWildcard = u($this->name)->indexOf('*') !== null;
        $names       = $hasWildcard ? [] : [$this->name];
        $values      = [];

        if ($hasWildcard) {
            if (!$this->className || !class_exists($this->className)) {
                return TsProperty::TYPE_UNKNOWN;
            }

            $constants = (new ReflectionClass($this->className))->getConstants(
                ReflectionClassConstant::IS_PUBLIC,
            );

            $names = array_keys(array_filter(
                $constants,
                fn (string $key): bool => fnmatch($this->name, $key),
                ARRAY_FILTER_USE_KEY,
            ));
        }

        foreach ($names as $name) {
            try {
                $values[] = $this->className
                    ? constant($this->className . '::' . $name)
                    : constant($name);
            } catch (Error) {
                return TsProperty::TYPE_UNKNOWN;
            }
        }

        return implode(' | ', array_map(
            fn (mixed $value): string => PhpStan::phpValueToTsType(
                $value,
                $this->indent ?? new Indent(),
                $this->quotes ?? new Quotes(),
            ),
            $values,
        )) ?: TsProperty::TYPE_UNKNOWN;
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
