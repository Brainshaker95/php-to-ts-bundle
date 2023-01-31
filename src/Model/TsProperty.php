<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Stringable;

use const PHP_EOL;

use function implode;
use function sprintf;

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
     */
    public function __construct(
        public string $name,
        public string|Node $type,
        public readonly bool $isReadonly = false,
        public readonly bool $isConstructorProperty = false,
        public readonly array $generics = [],
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly ?string $deprecation = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(Indent $indent = new Indent(), Quotes $quotes = new Quotes()): string
    {
        if ($this->type instanceof Node) {
            Converter::applyIndentAndQuotes([$this->type], $indent, $quotes, 2);
        }

        return sprintf(
            '%s%s%s%s: %s;',
            $indent->toString(),
            $this->getDocComment($indent),
            $this->isReadonly ? 'readonly ' : '',
            $this->name,
            $this->type,
        );
    }

    private function getDocComment(Indent $indent): string
    {
        if (!$this->summary && !$this->description && !$this->deprecation) {
            return '';
        }

        $docComment       = '/**' . PHP_EOL;
        $linePrefix       = $indent->toString() . ' * ';
        $summaryLines     = $this->summary ? Str::splitByNewLines($this->summary, $linePrefix) : null;
        $descriptionLines = $this->description ? Str::splitByNewLines($this->description, $linePrefix) : null;
        $hasSummary       = !empty($summaryLines);
        $hasDescription   = !empty($descriptionLines);

        if ($hasSummary) {
            $docComment .= implode(PHP_EOL, $summaryLines) . PHP_EOL;
        }

        if ($hasDescription) {
            $docComment .= implode(PHP_EOL, $descriptionLines) . PHP_EOL;
        }

        if ($this->deprecation) {
            if ($hasSummary || $hasDescription) {
                $docComment .= $linePrefix . PHP_EOL;
            }

            $docComment .= $linePrefix . $this->deprecation . PHP_EOL;
        }

        $docComment .= $indent->toString() . ' */' . PHP_EOL
            . $indent->toString();

        return $docComment;
    }
}
