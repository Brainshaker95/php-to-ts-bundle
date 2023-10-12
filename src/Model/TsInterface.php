<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Interface\Node;
use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\ConstructorFirst;
use Brainshaker95\PhpToTsBundle\Model\Config\TypeDefinitionType;
use Brainshaker95\PhpToTsBundle\Model\Traits\HasTsInterfaceHeader;
use Brainshaker95\PhpToTsBundle\Tool\Converter;
use Stringable;

use const PHP_EOL;

use function array_count_values;
use function array_filter;
use function array_map;
use function array_unshift;
use function count;
use function current;
use function implode;
use function in_array;
use function natcasesort;
use function sprintf;
use function usort;

final class TsInterface implements Stringable
{
    use HasTsInterfaceHeader;

    /**
     * @param TsGeneric[] $generics
     * @param true|string|null $deprecation
     * @param TsProperty[] $properties
     */
    public function __construct(
        public string $name,
        public ?string $parentName = null,
        public readonly bool $isReadonly = false,
        public readonly array $generics = [],
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
        $indent             = $this->config?->getIndent() ?? new Indent();
        $quotes             = $this->config?->getQuotes() ?? new Quotes();
        $typeDefinitionType = $this->config?->getTypeDefinitionType() ?? C::TYPE_DEFINITION_TYPE_DEFAULT;
        $isTypeAlias        = $typeDefinitionType === TypeDefinitionType::TYPE_TYPE_ALIAS;
        $isModule           = ($this->config?->getFileType() ?? C::FILE_TYPE_DEFAULT) === FileType::TYPE_MODULE;
        $imports            = $isModule ? $this->getImports() : [];
        $generics           = $this->getGenerics();

        $docComment = (new TsDocComment(
            description: $this->description,
            deprecation: $this->deprecation,
            generics: $generics,
        ))->toString();

        $parentName = $this->parentName ?? '';

        if ($parentName) {
            $parentName = $isTypeAlias
                ? (' ' . $parentName . ' &')
                : (' extends ' . $parentName);
        }

        $string = self::getHeader();

        if (count($imports)) {
            $string = $string
                ->append(implode(PHP_EOL, $imports))
                ->append(PHP_EOL)
                ->append(PHP_EOL)
            ;
        }

        if ($docComment) {
            $string = $string
                ->append($docComment)
                ->append(PHP_EOL)
            ;
        }

        $string = $string
            ->append($isModule ? 'export ' : 'declare ')
            ->append($typeDefinitionType . ' ')
            ->append($this->name)
            ->append(TsGeneric::multipleToString($generics, $indent, $quotes))
            ->append($isTypeAlias ? ' =' : '')
            ->append($parentName)
            ->append(' {')
            ->append(PHP_EOL)
        ;

        foreach ($this->getSortedProperties() as $property) {
            $string = $string
                ->append($property->toString())
                ->append(PHP_EOL)
            ;
        }

        return $string
            ->append('}')
            ->toString()
        ;
    }

    /**
     * Gets the file based on the configured file name strategy and file type.
     */
    public function getFileName(): string
    {
        $fileNameStrategy = $this->config?->getFileNameStrategy() ?? C::FILE_NAME_STRATEGY_DEFAULT;
        $fileType         = $this->config?->getFileType() ?? C::FILE_TYPE_DEFAULT;

        return (new $fileNameStrategy())->getName($this->name)
            . ($fileType === FileType::TYPE_DECLARATION ? '.d' : '')
            . '.ts';
    }

    /**
     * Gets the properties based on the configured sort strategy.
     *
     * @param ?class-string<SortStrategy>[] $sortStrategies
     *
     * @return TsProperty[]
     */
    public function getSortedProperties(?array $sortStrategies = null): array
    {
        $sortStrategies ??= $this->config?->getSortStrategies() ?? C::SORT_STRATEGIES_DEFAULT;
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
    private function getImports(): array
    {
        $imports      = $this->parentName ? [$this->parentName] : [];
        $genericNames = TsGeneric::getNames($this->generics);

        foreach ($this->properties as $property) {
            foreach ($property->classIdentifiers as $classIdentifier) {
                if (!in_array($classIdentifier, $imports, true)
                    && !in_array($classIdentifier, $genericNames, true)) {
                    $imports[] = $classIdentifier;
                }
            }
        }

        natcasesort($imports);

        $fileNameStrategy         = $this->config?->getFileNameStrategy() ?? C::FILE_NAME_STRATEGY_DEFAULT;
        $quotes                   = $this->config?->getQuotes() ?? new Quotes();
        $fileNameStrategyInstance = new $fileNameStrategy();

        return array_map(
            static fn (string $classIdentifier) => sprintf(
                'import type { %s } from %s',
                $classIdentifier,
                $quotes->toString('./' . $fileNameStrategyInstance->getName($classIdentifier)) . ';',
            ),
            $imports,
        );
    }

    /**
     * @return TsGeneric[]
     */
    private function getGenerics(): array
    {
        $generics = [];

        foreach ($this->getSortedProperties([ConstructorFirst::class]) as $property) {
            foreach ($property->generics as $generic) {
                $alreadyAddedGeneric = $property->isConstructorProperty
                    ? current(array_filter(
                        $generics,
                        static fn (TsGeneric $referenceGeneric) => $referenceGeneric->name === $generic->name,
                    ))
                    : false;

                if ($alreadyAddedGeneric) {
                    $generic = $alreadyAddedGeneric;
                } else {
                    $generics[] = $generic;
                }

                $generic->properties = [...$generic->properties, $property];
            }
        }

        foreach ($this->generics as $generic) {
            $propertiesWithGeneric = array_filter(
                $this->properties,
                static fn (TsProperty $property) => in_array($generic->name, $property->classIdentifiers, true),
            );

            if (!count($propertiesWithGeneric)) {
                continue;
            }

            array_unshift($generics, $generic);

            foreach ($propertiesWithGeneric as $propertyWithGeneric) {
                $generic->properties = [...$generic->properties, $propertyWithGeneric];
            }
        }

        self::renameGenerics($generics);
        usort($generics, static fn (TsGeneric $generic) => $generic->default ? 1 : -1);

        return $generics;
    }

    /**
     * @param TsGeneric[] $generics
     */
    private static function renameGenerics(array $generics): void
    {
        $usageCounts = array_count_values(TsGeneric::getNames($generics));
        $usedNames   = [];

        foreach ($generics as $generic) {
            $name = $generic->name;

            if ($usageCounts[$name] === 1) {
                continue;
            }

            $usedNames[$name] = ($usedNames[$name] ?? 0) + 1;
            $generic->name    = $name . $usedNames[$name];

            foreach ($generic->properties as $property) {
                self::applyNewGenericNameToProperty(
                    property: $property,
                    oldName: $name,
                    newName: $generic->name,
                    generics: $generics,
                );
            }
        }
    }

    /**
     * @param TsGeneric[] $generics
     * @param ?Node[] $nodes
     */
    private static function applyNewGenericNameToProperty(
        TsProperty $property,
        string $oldName,
        string $newName,
        array $generics,
        ?array $nodes = null,
    ): void {
        if (!$property->type instanceof Node) {
            return;
        }

        $nodes ??= [$property->type];

        foreach ($nodes as $node) {
            $classIdentifierNode = Converter::getClassIdentifierNode($node);

            if ($classIdentifierNode) {
                foreach ($generics as $generic) {
                    if ($generic->name === $newName && $classIdentifierNode->name === $oldName) {
                        $classIdentifierNode->name = $generic->name;
                    }
                }
            }

            $nextLevelNodes = Converter::getNextLevelNodes($node);

            if (count($nextLevelNodes)) {
                self::applyNewGenericNameToProperty(
                    property: $property,
                    oldName: $oldName,
                    newName: $newName,
                    generics: $generics,
                    nodes: $nextLevelNodes,
                );
            }
        }
    }
}
