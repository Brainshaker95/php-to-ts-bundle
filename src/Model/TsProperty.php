<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Stringable;

use const PHP_EOL;

use function Symfony\Component\String\u;

final class TsProperty implements Stringable
{
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_FALSE   = 'false';
    public const TYPE_NULL    = 'null';
    public const TYPE_NUMBER  = 'number';
    public const TYPE_STRING  = 'string';
    public const TYPE_THIS    = 'this';
    public const TYPE_TRUE    = 'true';
    public const TYPE_UNKNOWN = 'unknown';

    /**
     * @param self::TYPE_UNKNOWN|Node $type
     * @param TsGeneric[] $generics
     * @param string[] $classIdentifiers
     * @phpstan-param array<value-of<TsDocComment::SUPPORTED_TAGS>, string> $tags
     */
    public function __construct(
        public string $name,
        public string|Node $type,
        public bool $isReadonly = false,
        public bool $isConstructorProperty = false,
        public bool $isEnumProperty = false,
        public array $classIdentifiers = [],
        public array $generics = [],
        public bool $doesRequireValueOf = false,
        public ?string $summary = null,
        public ?string $description = null,
        public array $tags = [],
        public ?C $config = null,
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $indent = $this->config?->getIndent() ?? new Indent();
        $quotes = $this->config?->getQuotes() ?? new Quotes();

        if ($this->type instanceof Node) {
            Converter::applyIndentAndQuotes([$this->type], $indent, $quotes);
        }

        $docComment = (new TsDocComment(
            summary: $this->summary,
            description: $this->description,
            tags: $this->tags,
        ))->toString($indent);

        return u($docComment ? ($docComment . PHP_EOL) : '')
            ->append($indent->toString())
            ->append($this->isReadonly ? 'readonly ' : '')
            ->append($this->name . ': ')
            ->append((string) $this->type)
            ->append($this->isEnumProperty ? ',' : ';')
            ->toString()
        ;
    }
}
