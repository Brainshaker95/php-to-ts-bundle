<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasTsInterfaceHeader;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Stringable;

use const PHP_EOL;

final class TsEnum implements Stringable
{
    use HasTsInterfaceHeader;

    /**
     * @param Converter::TYPE_INT|Converter::TYPE_STRING $scalarType
     * @param true|string|null $deprecation
     * @param TsProperty[] $properties
     */
    public function __construct(
        public string $name,
        public readonly string $scalarType,
        public ?string $description = null,
        public bool|string|null $deprecation = null,
        public array $properties = [],
        public ?C $config = null,
    ) {}

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

        $docComment = (new TsDocComment(
            description: $this->description,
            deprecation: $this->deprecation,
        ))->toString();

        $string = self::getHeader();

        if ($docComment) {
            $string = $string
                ->append($docComment)
                ->append(PHP_EOL)
            ;
        }

        $string = $string
            ->append('export const ')
            ->append($upperSnakeName)
            ->append(' = <const>{')
            ->append(PHP_EOL)
        ;

        foreach ($this->properties as $property) {
            $string = $string
                ->append($property->toString())
                ->append(PHP_EOL)
            ;
        }

        return $string
            ->append('} satisfies Record<string, ')
            ->append(Converter::NON_ITERABLE_TYPE_MAP[$this->scalarType])
            ->append('>;')
            ->append(PHP_EOL)
            ->append(PHP_EOL)
            ->append('export type ')
            ->append($this->name)
            ->append(' = typeof ')
            ->append($upperSnakeName)
            ->append('[keyof typeof ')
            ->append($upperSnakeName)
            ->append('];')
            ->toString()
        ;
    }

    /**
     * Gets the file based on the configured file name strategy.
     */
    public function getFileName(): string
    {
        $fileNameStrategy = $this->config?->getFileNameStrategy() ?? C::FILE_NAME_STRATEGY_DEFAULT;

        return (new $fileNameStrategy())->getName($this->name) . '.ts';
    }
}
