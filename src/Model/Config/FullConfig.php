<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Tool\Assert;

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
    ) {
    }

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
     * @param array{
     *     input_dir: string,
     *     output_dir: string,
     *     file_type: string,
     *     type_definition_type: string,
     *     indent: array{
     *         style: ?string,
     *         count: ?int<0,max>,
     *     },
     *     quotes: string,
     *     sort_strategies: non-empty-string[],
     *     file_name_strategy: string,
     * } $values
     */
    public static function fromArray(array $values): self
    {
        $fileType = Assert::inStringArrayNonNullable(
            $values[C::FILE_TYPE_KEY],
            C::FILE_TYPE_VALID_VALUES,
        );

        $typeDefinitionType = Assert::inStringArrayNonNullable(
            $values[C::TYPE_DEFINITION_TYPE_KEY],
            C::TYPE_DEFINITION_TYPE_VALID_VALUES,
        );

        $indentStyle = Assert::inStringArrayNullable(
            $values[C::INDENT_KEY][C::INDENT_STYLE_KEY],
            C::INDENT_STYLE_VALID_VALUES,
        );

        $quotes = Assert::inStringArrayNonNullable(
            $values[C::QUOTES_KEY],
            C::QUOTES_VALID_VALUES,
        );

        $sortStrategies = Assert::interfaceClassStringArrayNonNullable(
            $values[C::SORT_STRATEGIES_KEY],
            SortStrategy::class,
        );

        $fileNameStrategy = Assert::interfaceClassStringNonNullable(
            $values[C::FILE_NAME_STRATEGY_KEY],
            FileNameStrategy::class,
        );

        return new self(
            inputDir: $values[C::INPUT_DIR_KEY],
            outputDir: $values[C::OUTPUT_DIR_KEY],
            fileType: $fileType,
            typeDefinitionType: $typeDefinitionType,
            indent: new Indent(
                style: $indentStyle ?? C::INDENT_STYLE_DEFAULT,
                count: $values[C::INDENT_KEY][C::INDENT_COUNT_KEY] ?? C::INDENT_COUNT_DEFAULT,
            ),
            quotes: new Quotes($quotes),
            sortStrategies: $sortStrategies,
            fileNameStrategy: $fileNameStrategy,
        );
    }
}
