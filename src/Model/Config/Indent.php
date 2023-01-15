<?php

namespace Brainshaker95\PhpToTsBundle\Model\Config;

use Brainshaker95\PhpToTsBundle\Interface\Config;

class Indent
{
    public const STYLE_SPACE = 'space';
    public const STYLE_TAB   = 'tab';

    /**
     * @param self::STYLE_* $style
     * @param int<0,max> $count
     */
    public function __construct(
        public readonly string $style = Config::INDENT_STYLE_DEFAULT,
        public readonly int $count = Config::INDENT_COUNT_DEFAULT,
    ) {
    }
}
