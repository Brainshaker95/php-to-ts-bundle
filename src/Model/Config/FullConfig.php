<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Tool\Assert;

/**
 * @phpstan-import-type ConfigArray from C
 */
final class FullConfig implements C
{
    /**
     * @phpstan-param FileType::TYPE_* $fileType
     * @phpstan-param TypeDefinitionType::TYPE_* $typeDefinitionType
     * @param class-string<SortStrategy>[] $sortStrategies
     * @param class-string<FileNameStrategy> $fileNameStrategy
     */
    public function __construct(
        private string $inputDir,
        private string $outputDir,
        private string $fileType,
        private string $typeDefinitionType,
        private Indent $indent,
        private Quotes $quotes,
        private array $sortStrategies,
        private string $fileNameStrategy,
    ) {}

    public function getInputDir(): string
    {
        return $this->inputDir;
    }

    public function setInputDir(string $inputDir): self
    {
        $this->inputDir = $inputDir;

        return $this;
    }

    public function getOutputDir(): string
    {
        return $this->outputDir;
    }

    public function setOutputDir(string $outputDir): self
    {
        $this->outputDir = $outputDir;

        return $this;
    }

    /**
     * @phpstan-return FileType::TYPE_*
     */
    public function getFileType(): string
    {
        return $this->fileType;
    }

    /**
     * @phpstan-param FileType::TYPE_* $fileType
     */
    public function setFileType(string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * @phpstan-return TypeDefinitionType::TYPE_*
     */
    public function getTypeDefinitionType(): string
    {
        return $this->typeDefinitionType;
    }

    /**
     * @phpstan-param TypeDefinitionType::TYPE_* $typeDefinitionType
     */
    public function setTypeDefinitionType(string $typeDefinitionType): self
    {
        $this->typeDefinitionType = $typeDefinitionType;

        return $this;
    }

    public function getIndent(): Indent
    {
        return $this->indent;
    }

    public function setIndent(Indent $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    public function getQuotes(): Quotes
    {
        return $this->quotes;
    }

    public function setQuotes(Quotes $quotes): self
    {
        $this->quotes = $quotes;

        return $this;
    }

    /**
     * @return class-string<SortStrategy>[]
     */
    public function getSortStrategies(): array
    {
        return $this->sortStrategies;
    }

    /**
     * @param class-string<SortStrategy>[] $sortStrategies
     */
    public function setSortStrategies(array $sortStrategies): self
    {
        $this->sortStrategies = $sortStrategies;

        return $this;
    }

    /**
     * @return class-string<FileNameStrategy>
     */
    public function getFileNameStrategy(): string
    {
        return $this->fileNameStrategy;
    }

    /**
     * @param class-string<FileNameStrategy> $fileNameStrategy
     */
    public function setFileNameStrategy(string $fileNameStrategy): self
    {
        $this->fileNameStrategy = $fileNameStrategy;

        return $this;
    }

    /**
     * @phpstan-param ConfigArray $array
     */
    public static function fromArray(array $array): self
    {
        $fileType = Assert::inStringArrayNonNullable(
            $array[C::FILE_TYPE_KEY] ?? null,
            C::FILE_TYPE_VALID_VALUES,
        );

        $typeDefinitionType = Assert::inStringArrayNonNullable(
            $array[C::TYPE_DEFINITION_TYPE_KEY] ?? null,
            C::TYPE_DEFINITION_TYPE_VALID_VALUES,
        );

        $indentStyle = Assert::inStringArrayNullable(
            $array[C::INDENT_KEY][C::INDENT_STYLE_KEY] ?? null,
            C::INDENT_STYLE_VALID_VALUES,
        );

        $quotes = Assert::inStringArrayNonNullable(
            $array[C::QUOTES_KEY] ?? null,
            C::QUOTES_VALID_VALUES,
        );

        $sortStrategies = Assert::interfaceClassStringArrayNonNullable(
            $array[C::SORT_STRATEGIES_KEY] ?? null,
            SortStrategy::class,
        );

        $fileNameStrategy = Assert::interfaceClassStringNonNullable(
            $array[C::FILE_NAME_STRATEGY_KEY] ?? null,
            FileNameStrategy::class,
        );

        return new self(
            inputDir: $array[C::INPUT_DIR_KEY] ?? C::INPUT_DIR_DEFAULT,
            outputDir: $array[C::OUTPUT_DIR_KEY] ?? C::OUTPUT_DIR_DEFAULT,
            fileType: $fileType,
            typeDefinitionType: $typeDefinitionType,
            indent: new Indent(
                style: $indentStyle ?? C::INDENT_STYLE_DEFAULT,
                count: $array[C::INDENT_KEY][C::INDENT_COUNT_KEY] ?? C::INDENT_COUNT_DEFAULT,
            ),
            quotes: new Quotes($quotes),
            sortStrategies: $sortStrategies,
            fileNameStrategy: $fileNameStrategy,
        );
    }
}
