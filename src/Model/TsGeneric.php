<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Stringable;

use const PHP_EOL;

use function array_filter;
use function array_map;
use function implode;
use function sprintf;

/**
 * @internal
 */
final class TsGeneric implements Stringable
{
    public function __construct(
        public string $name,
        public ?Node $bound = null,
        public ?Node $default = null,
        public ?string $description = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(
        Indent $indent = new Indent(),
        Quotes $quotes = new Quotes(),
    ): string {
        Converter::applyIndentAndQuotes(array_filter([$this->bound, $this->default]), $indent, $quotes);

        return sprintf(
            '%s%s%s',
            $this->name,
            $this->bound ? ' extends ' . $this->bound->toString() : '',
            $this->default ? ' = ' . $this->default->toString() : '',
        );
    }

    public function getTemplateTag(): string
    {
        if (!$this->description) {
            return '';
        }

        return '@template ' . $this->name . ' ' . $this->description;
    }

    /**
     * @param self[] $generics
     */
    public static function multipleToString(
        array $generics,
        Indent $indent = new Indent(),
        Quotes $quotes = new Quotes(),
    ): string {
        if (empty($generics)) {
            return '';
        }

        return '<' . PHP_EOL
            . implode(',' . PHP_EOL, array_map(
                static fn (TsGeneric $generic) => $indent->toString() . $generic->toString($indent, $quotes),
                $generics,
            )) . ',' . PHP_EOL
            . '>';
    }
}
