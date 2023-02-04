<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Stringable;

use const PHP_EOL;

use function array_filter;
use function array_map;
use function count;
use function implode;
use function in_array;
use function sort;
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
        public ?string $description = null,
        public ?string $deprecation = null,
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
     * @param class-string<FileNameStrategy> $fileNameStrategy
     */
    public function toString(
        string $fileType = C::FILE_TYPE_DEFAULT,
        Indent $indent = new Indent(),
        Quotes $quotes = new Quotes(),
        array $sortStrategies = C::SORT_STRATEGIES_DEFAULT,
        string $fileNameStrategy = C::FILE_NAME_STRATEGY_DEFAULT,
    ): string {
        $isModule = $fileType === FileType::TYPE_MODULE;
        $generics = $this->getGenerics($indent, $quotes);
        $imports  = $isModule ? $this->getImports($fileNameStrategy, $quotes) : [];

        return '/*' . PHP_EOL
            . ' * Auto-generated by PhpToTsBundle' . PHP_EOL
            . ' * Do not modify directly!' . PHP_EOL
            . ' */' . PHP_EOL . PHP_EOL
            . (!empty($imports) ? implode(PHP_EOL, $imports) . PHP_EOL . PHP_EOL : '')
            . $this->getDocComment()
            . sprintf(
                '%s interface %s%s%s {' . PHP_EOL . '%s' . PHP_EOL . '}',
                $isModule ? 'export' : 'declare',
                $this->name,
                !empty($generics) ? '<' . implode(', ', $generics) . '>' : '',
                $this->parentName ? ' extends ' . $this->parentName : '',
                implode(PHP_EOL, array_map(
                    static fn (TsProperty $property) => $property->toString($indent, $quotes),
                    self::getSortedProperties($sortStrategies),
                )),
            );
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

    private function getDocComment(): string
    {
        if (!$this->description && !$this->deprecation) {
            return '';
        }

        $docComment       = '/**' . PHP_EOL;
        $linePrefix       = ' * ';
        $descriptionLines = $this->description ? Str::splitByNewLines($this->description, $linePrefix) : null;
        $hasDescription   = !empty($descriptionLines);

        if ($hasDescription) {
            $docComment .= implode(PHP_EOL, $descriptionLines) . PHP_EOL;
        }

        if ($this->deprecation) {
            if ($hasDescription) {
                $docComment .= $linePrefix . PHP_EOL;
            }

            $docComment .= $linePrefix . $this->deprecation . PHP_EOL;
        }

        $docComment .= ' */' . PHP_EOL;

        return $docComment;
    }

    /**
     * @param class-string<SortStrategy>[] $sortStrategies
     *
     * @return TsProperty[]
     */
    private function getSortedProperties(array $sortStrategies): array
    {
        $properties = $this->properties;

        foreach ($sortStrategies as $sortStrategy) {
            usort(
                $properties,
                static fn (TsProperty $prop1, TsProperty $prop2) => (new $sortStrategy())->sort($prop1, $prop2),
            );
        }

        return $properties;
    }

    /**
     * @return string[]
     */
    private function getGenerics(Indent $indent, Quotes $quotes): array
    {
        $generics                     = [];
        $genericNames                 = [];
        $usedNames                    = [];
        $constructorPropertiesHandled = false;

        foreach ($this->properties as $property) {
            if ($property->isConstructorProperty) {
                if ($constructorPropertiesHandled) {
                    continue;
                }

                $constructorPropertiesHandled = true;
            }

            foreach ($property->generics as $generic) {
                $genericNames[] = $generic->name;
            }
        }

        $constructorPropertiesHandled = false;

        foreach ($this->properties as $property) {
            if ($property->isConstructorProperty) {
                if ($constructorPropertiesHandled) {
                    continue;
                }

                $constructorPropertiesHandled = true;
            }

            foreach ($property->generics as $generic) {
                $name = $generic->name;

                $usageCount = count(array_filter(
                    $genericNames,
                    static fn (string $genericName) => $genericName === $name,
                ));

                $usedNames[$name] ??= 0;
                $usedNames[$name] += 1;

                $genericString = $generic->toString(
                    $usageCount === 1 ? $name : $name . $usedNames[$name],
                    $indent,
                    $quotes,
                );

                $generics[] = $genericString;
            }
        }

        return $generics;
    }

    /**
     * @param class-string<FileNameStrategy> $fileNameStrategy
     *
     * @return string[]
     */
    private function getImports(string $fileNameStrategy, Quotes $quotes): array
    {
        $imports = $this->parentName ? [$this->parentName] : [];

        foreach ($this->properties as $property) {
            if (empty($property->classIdentifiers)) {
                continue;
            }

            foreach ($property->classIdentifiers as $classIdentifier) {
                if (!in_array($classIdentifier, $imports, true)) {
                    $imports[] = $classIdentifier;
                }
            }
        }

        sort($imports);

        return array_map(
            static fn (string $classIdentifier) => sprintf(
                'import type { %s } from %s',
                $classIdentifier,
                $quotes->toString('./' . (new $fileNameStrategy())->getName($classIdentifier)) . ';',
            ),
            $imports,
        );
    }
}
