<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Stringable;

use const PHP_EOL;

use function array_filter;
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
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly bool|string|null $deprecation = null,
        public readonly array $generics = [],
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(?Indent $indent = null): string
    {
        $linePrefix = ' * ';

        $content = u(self::linesToString(
            lines: Str::splitByNewLines($this->summary ?? '', $linePrefix),
            linePrefix: $linePrefix,
            indent: $indent,
        ));

        $previousDescriptionLine = '';

        $descriptionLines = array_filter(Str::splitByNewLines(
            string: $this->description ?? '',
            removeEmptyLines: false,
            lineCallback: static function (string $line) use (&$previousDescriptionLine, $linePrefix) {
                if ($line === '' && $previousDescriptionLine !== '') {
                    $previousDescriptionLine = $line;

                    return $linePrefix . "\n";
                }

                $previousDescriptionLine = $line;

                return $line ? $linePrefix . $line : '';
            },
        ));

        $content = $content->append(self::linesToString(
            lines: $descriptionLines,
            linePrefix: $linePrefix,
            hasPreviousLines: (bool) $content->trim()->length(),
            indent: $indent,
        ));

        $deprecationLines = match (true) {
            default                       => [],
            $this->deprecation            => [$linePrefix . '@deprecated'],
            is_string($this->deprecation) => Str::splitByNewLines(
                string: $this->deprecation,
                lineCallback: static fn (string $line, int $index) => $index === 0
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

        $templateTagLines = [];

        foreach ($this->generics as $generic) {
            $templateTagLines = [
                ...$templateTagLines,
                ...Str::splitByNewLines($generic->getTemplateTag(), $linePrefix),
            ];
        }

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
            ->append(' */')
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
            ? (PHP_EOL . $indent?->toString() . rtrim($linePrefix) . PHP_EOL . $linesString)
            : $linesString;
    }
}
