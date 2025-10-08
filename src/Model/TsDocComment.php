<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Override;
use Stringable;
use Symfony\Component\String\UnicodeString;

use const PHP_EOL;

use function array_values;
use function Symfony\Component\String\u;

/**
 * @internal
 */
final class TsDocComment implements Stringable
{
    public const TAG_API        = 'api';
    public const TAG_AUTHOR     = 'author';
    public const TAG_CATEGORY   = 'category';
    public const TAG_COPYRIGHT  = 'copyright';
    public const TAG_DEPRECATED = 'deprecated';
    public const TAG_INTERNAL   = 'internal';
    public const TAG_LICENSE    = 'license';
    public const TAG_LINK       = 'link';
    public const TAG_PACKAGE    = 'package';
    public const TAG_SEE        = 'see';
    public const TAG_SINCE      = 'since';
    public const TAG_SUBPACKAGE = 'subpackage';
    public const TAG_TODO       = 'todo';
    public const TAG_VERSION    = 'version';

    public const SUPPORTED_TAGS = [
        self::TAG_API,
        self::TAG_AUTHOR,
        self::TAG_CATEGORY,
        self::TAG_COPYRIGHT,
        self::TAG_DEPRECATED,
        self::TAG_INTERNAL,
        self::TAG_LICENSE,
        self::TAG_LINK,
        self::TAG_PACKAGE,
        self::TAG_SEE,
        self::TAG_SINCE,
        self::TAG_SUBPACKAGE,
        self::TAG_TODO,
        self::TAG_VERSION,
    ];

    private const LINE_PREFIX = ' * ';

    /**
     * @phpstan-param array<value-of<self::SUPPORTED_TAGS>, string> $tags
     * @param TsGeneric[] $generics
     */
    public function __construct(
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly array $tags = [],
        public readonly array $generics = [],
    ) {}

    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(?Indent $indent = null): string
    {
        $content = u();

        if (!Str::isEmpty($this->summary)) {
            $content = self::appendPadded(
                content: $content,
                stringToAppend: $this->summary,
                indent: $indent,
            );
        }

        if (!Str::isEmpty($this->description)) {
            $content = self::appendPadded(
                content: $content,
                stringToAppend: $this->description,
                indent: $indent,
            );
        }

        foreach (array_values($this->tags) as $index => $renderedTag) {
            if (!Str::isEmpty($renderedTag)) {
                $content = self::appendPadded(
                    content: $content,
                    stringToAppend: $renderedTag,
                    indent: $indent,
                    index: $index,
                );
            }
        }

        foreach (array_values($this->generics) as $index => $generic) {
            $templateTag = $generic->getTemplateTag();

            if (!Str::isEmpty($templateTag)) {
                $content = self::appendPadded(
                    content: $content,
                    stringToAppend: $templateTag,
                    indent: $indent,
                    index: $index,
                );
            }
        }

        if (Str::isEmpty($content->toString())) {
            return '';
        }

        return u($indent?->toString() ?? '')
            ->append('/**')
            ->append(PHP_EOL)
            ->append(Str::trimEnd($content->toString(), self::LINE_PREFIX))
            ->append(PHP_EOL)
            ->append($indent?->toString() ?? '')
            ->append(' */')
            ->toString()
        ;
    }

    private static function appendPadded(
        UnicodeString $content,
        string $stringToAppend,
        ?Indent $indent,
        int $index = 0,
    ): UnicodeString {
        if (!$stringToAppend) {
            return $content;
        }

        static $previousWasMultiline;

        $previousWasMultiline ??= false;
        $isMutliline = Str::containsNewlines($stringToAppend);

        $emptyLine = u($indent?->toString() ?? '')
            ->append(Str::trimEnd(self::LINE_PREFIX))
            ->append(PHP_EOL)
            ->toString()
        ;

        if ($index > 0 && !($previousWasMultiline || $isMutliline)) {
            $content = u(Str::trimEnd($content->toString(), self::LINE_PREFIX))
                ->append(PHP_EOL)
            ;
        }

        $previousWasMultiline = $isMutliline;

        return $content->append(Str::indentAndPrefixLines(
            string: $stringToAppend,
            indent: $indent,
            prefix: self::LINE_PREFIX,
        ) . PHP_EOL . $emptyLine);
    }
}
