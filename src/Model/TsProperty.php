<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Config;
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
     * @param true|string|null $deprecation
     */
    public function __construct(
        public string $name,
        public string|Node $type,
        public readonly bool $isReadonly = false,
        public readonly bool $isConstructorProperty = false,
        public readonly array $classIdentifiers = [],
        public readonly array $generics = [],
        public readonly ?string $description = null,
        public bool|string|null $deprecation = null,
        public ?Config $config = null,
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
            description: $this->description,
            deprecation: $this->deprecation,
        ))->toString($indent);

        return u($docComment ? ($docComment . PHP_EOL) : '')
            ->append($indent->toString())
            ->append($this->isReadonly ? 'readonly ' : '')
            ->append($this->name . ': ')
            ->append((string) $this->type . ';')
            ->toString()
        ;
    }
}
