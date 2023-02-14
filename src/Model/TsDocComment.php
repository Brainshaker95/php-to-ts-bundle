<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Stringable;

use const PHP_EOL;

use function implode;
use function is_string;
use function rtrim;
use function trim;

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
        $linePrefix       = ' * ';
        $descriptionLines = Str::splitByNewLines($this->description ?? '', $linePrefix);
        $content          = self::linesToString($descriptionLines, $linePrefix, false, $indent);
        $templateTagLines = [];

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

        $content .= self::linesToString($deprecationLines, $linePrefix, (bool) trim($content), $indent);

        foreach ($this->generics as $generic) {
            $templateTagLines = [
                ...$templateTagLines,
                ...Str::splitByNewLines($generic->getTemplateTag(), $linePrefix),
            ];
        }

        $content .= self::linesToString($templateTagLines, $linePrefix, (bool) trim($content), $indent);

        if (!trim($content)) {
            return '';
        }

        return $indent?->toString() . '/**' . PHP_EOL
            . $content . PHP_EOL
            . $indent?->toString() . ' */';
    }

    /**
     * @param string[] $lines
     */
    private static function linesToString(
        array $lines,
        string $linePrefix,
        bool $hasPreviousLines,
        ?Indent $indent = null,
    ): string {
        $linesString = rtrim($indent?->toString() . implode(PHP_EOL . $indent?->toString(), $lines));

        return $hasPreviousLines && !empty($lines)
            ? PHP_EOL . $indent?->toString() . $linePrefix . PHP_EOL . $linesString
            : $linesString;
    }
}
