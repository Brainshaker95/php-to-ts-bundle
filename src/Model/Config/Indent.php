<?php

namespace Brainshaker95\PhpToTsBundle\Model\Config;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Stringable;

class Indent implements Stringable
{
    public const STYLE_SPACE = 'space';
    public const STYLE_TAB   = 'tab';

    /**
     * @phpstan-param self::STYLE_* $style
     *
     * @param int<0,max> $count
     */
    public function __construct(
        public readonly string $style = C::INDENT_STYLE_DEFAULT,
        public readonly int $count = C::INDENT_COUNT_DEFAULT,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return str_repeat(
            $this->style === self::STYLE_TAB ? "\t" : ' ',
            $this->count,
        );
    }
}
