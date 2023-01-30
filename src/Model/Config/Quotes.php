<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Stringable;

final class Quotes implements Stringable
{
    public const STYLE_DOUBLE = 'double';
    public const STYLE_SINGLE = 'single';

    public const QUOTES_MAP = [
        self::STYLE_DOUBLE => '"',
        self::STYLE_SINGLE => '\'',
    ];

    /**
     * @phpstan-param self::STYLE_* $style
     */
    public function __construct(
        public readonly string $style = C::QUOTES_DEFAULT,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(?string $content = null): string
    {
        $quotes = self::QUOTES_MAP[$this->style];

        return $content !== null
            ? $quotes . $content . $quotes
            : $quotes;
    }

    public static function default(string $content): string
    {
        $quotes = self::QUOTES_MAP[C::QUOTES_DEFAULT];

        return $quotes . $content . $quotes;
    }
}
