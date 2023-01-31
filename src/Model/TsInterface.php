<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Stringable;

use const PHP_EOL;

use function implode;
use function in_array;
use function sprintf;
use function usort;

final class TsInterface implements Stringable
{
    /**
     * @param TsProperty[] $properties
     */
    public function __construct(
        public string $name,
        public ?string $parentName = null,
        public array $properties = [],
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function addProperty(TsProperty $property): self
    {
        $this->properties[] = $property;

        return $this;
    }

    /**
     * @phpstan-param FileType::TYPE_* $fileType
     * @param class-string<SortStrategy>[] $sortStrategies
     */
    public function toString(
        string $fileType = C::FILE_TYPE_DEFAULT,
        Indent $indent = new Indent(),
        Quotes $quotes = new Quotes(),
        array $sortStrategies = [],
    ): string {
        $generics = $this->getGenerics($indent, $quotes);

        $string = '/*' . PHP_EOL
            . ' * Auto-generated by PhpToTsBundle' . PHP_EOL
            . ' * Do not modify directly!' . PHP_EOL
            . ' */' . PHP_EOL
            . sprintf(
                '%s interface %s%s%s {',
                $fileType === FileType::TYPE_MODULE ? 'export' : 'declare',
                $this->name,
                !empty($generics) ? '<' . implode(', ', $generics) . '>' : '',
                $this->parentName ? ' extends ' . $this->parentName : '',
            )
            . PHP_EOL;

        $properties = $this->properties;

        foreach ($sortStrategies as $sortStrategy) {
            usort(
                $properties,
                static fn (TsProperty $prop1, TsProperty $prop2) => (new $sortStrategy())->sort($prop1, $prop2),
            );
        }

        foreach ($properties as $property) {
            $string .= $property->toString($indent, $quotes) . PHP_EOL;
        }

        $string .= '}';

        return $string;
    }

    /**
     * @phpstan-param FileType::TYPE_* $fileType
     * @param class-string<FileNameStrategy> $fileNameStrategy
     */
    public function getFileName(
        string $fileType = C::FILE_TYPE_DEFAULT,
        string $fileNameStrategy = C::FILE_NAME_STRATEGY_DEFAULT,
    ): string {
        return (new $fileNameStrategy())->getName($this->name)
            . ($fileType === FileType::TYPE_DECLARATION ? '.d' : '')
            . '.ts';
    }

    /**
     * @return string[]
     */
    private function getGenerics(Indent $indent, Quotes $quotes): array
    {
        $generics  = [];
        $usedNames = [];

        foreach ($this->properties as $property) {
            foreach ($property->generics as $generic) {
                if (in_array($generic->name, $usedNames, true)) {
                    continue;
                }

                $generics[]  = $generic->toString($indent, $quotes);
                $usedNames[] = $generic->name;
            }
        }

        return $generics;
    }
}
