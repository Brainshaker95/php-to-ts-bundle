<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Stringable;

use function array_filter;
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
}
