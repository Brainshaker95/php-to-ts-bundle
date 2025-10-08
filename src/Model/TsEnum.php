<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasFileName;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasTsInterfaceHeader;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Override;
use Stringable;

use const PHP_EOL;

final class TsEnum implements Stringable
{
    use HasFileName;
    use HasTsInterfaceHeader;

    /**
     * @param Converter::TYPE_INT|Converter::TYPE_STRING $scalarType
     * @phpstan-param array<value-of<TsDocComment::SUPPORTED_TAGS>, string> $tags
     * @param TsProperty[] $properties
     */
    public function __construct(
        public string $name,
        public string $scalarType,
        public ?string $summary = null,
        public ?string $description = null,
        public array $tags = [],
        public array $properties = [],
        public ?C $config = null,
    ) {}

    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    public function addProperty(TsProperty $property): self
    {
        $this->properties[] = $property;

        return $this;
    }

    public function toString(): string
    {
        $upperSnakeName = Str::toUpper(Str::toSnake($this->name));
        $isModule       = ($this->config?->getFileType() ?? C::FILE_TYPE_DEFAULT) === FileType::TYPE_MODULE;

        $docComment = (new TsDocComment(
            summary: $this->summary,
            description: $this->description,
            tags: $this->tags,
        ))->toString();

        $string = self::getHeader();

        if ($docComment) {
            $string = $string
                ->append($docComment)
                ->append(PHP_EOL)
            ;
        }

        $string = $string
            ->append($isModule ? 'export const ' : 'declare var ')
            ->append($upperSnakeName)
            ->append($isModule ? ' = <const>{' : ': {')
            ->append(PHP_EOL)
        ;

        foreach ($this->properties as $property) {
            $string = $string
                ->append($property->toString())
                ->append(PHP_EOL)
            ;
        }

        return $string
            ->append($isModule ? '} satisfies Record<string, ' : '')
            ->append($isModule ? Converter::SIMPLE_TYPES[$this->scalarType] : '')
            ->append($isModule ? '>;' : '};')
            ->append(PHP_EOL)
            ->append(PHP_EOL)
            ->append($docComment ? $docComment . PHP_EOL : '')
            ->append($isModule ? 'export type ' : 'declare type ')
            ->append($this->name)
            ->append(' = typeof ')
            ->append($upperSnakeName)
            ->append('[keyof typeof ')
            ->append($upperSnakeName)
            ->append('];')
            ->toString()
        ;
    }
}
