<?php

namespace Brainshaker95\PhpToTsBundle\Model\Config;

class Indent
{
    public const STYLE_SPACE = 'space';
    public const STYLE_TAB   = 'tab';

    /**
     * @param self::STYLE_* $style
     */
    public function __construct(
        public readonly string $style = self::STYLE_SPACE,
        public readonly int $count = 2,
    ) {
    }
}
