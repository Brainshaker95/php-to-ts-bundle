<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Stringable;

use const PHP_EOL;

use function array_merge;
use function array_reduce;
use function count;
use function implode;
use function is_string;
use function rtrim;
use function Symfony\Component\String\u;

/**
 * @internal
 */
final class TsDocComment implements Stringable
{
    /**
     * @param true|string|null $deprecation
     * @param TsGeneric[] $generics
     */
    public function __construct(
        public readonly ?string $description = null,
        public readonly bool|string|null $deprecation = null,
        public readonly array $generics = [],
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(?Indent $indent = null): string
    {
        $linePrefix = ' * ';

        $content = u(self::linesToString(
            lines: Str::splitByNewLines($this->description ?? '', $linePrefix),
            linePrefix: $linePrefix,
            indent: $indent,
        ));

        $deprecationLines = match (true) {
            default                       => [],
            $this->deprecation            => [$linePrefix . '@deprecated'],
            is_string($this->deprecation) => Str::splitByNewLines(
                $this->deprecation,
                $linePrefix,
                static fn (string $line, int $index) => $index === 0
                    ? $linePrefix . '@deprecated ' . $line
                    : $linePrefix . $line,
            ),
        };

        $content = $content->append(self::linesToString(
            lines: $deprecationLines,
            linePrefix: $linePrefix,
            hasPreviousLines: (bool) $content->trim()->length(),
            indent: $indent,
        ));

        $templateTagLines = array_reduce(
            $this->generics,
            static fn ($lines, $generic) => array_merge(
                $lines,
                Str::splitByNewLines($generic->getTemplateTag(), $linePrefix),
            ),
            [],
        );

        $content = $content->append(self::linesToString(
            lines: $templateTagLines,
            linePrefix: $linePrefix,
            hasPreviousLines: (bool) $content->trim()->length(),
            indent: $indent,
        ));

        if (!$content->trim()->length()) {
            return '';
        }

        return u($indent?->toString() ?? '')
            ->append('/**')
            ->append(PHP_EOL)
            ->append($content->toString())
            ->append(PHP_EOL)
            ->append($indent?->toString() ?? '')
            ->append('*/')
            ->toString()
        ;
    }

    /**
     * @param string[] $lines
     */
    private static function linesToString(
        array $lines,
        string $linePrefix,
        ?Indent $indent,
        bool $hasPreviousLines = false,
    ): string {
        $linesString = rtrim($indent?->toString() . implode(PHP_EOL . $indent?->toString(), $lines));

        return $hasPreviousLines && count($lines)
            ? (PHP_EOL . $indent?->toString() . $linePrefix . PHP_EOL . $linesString)
            : $linesString;
    }
}
