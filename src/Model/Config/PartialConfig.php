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
final class PartialConfig implements C
{
    /**
     * @phpstan-param ?FileType::TYPE_* $fileType
     * @phpstan-param TypeDefinitionType::TYPE_* $typeDefinitionType
     * @param ?class-string<SortStrategy>[] $sortStrategies
     * @param ?class-string<FileNameStrategy> $fileNameStrategy
     */
    public function __construct(
        private ?string $inputDir = null,
        private ?string $outputDir = null,
        private ?string $fileType = null,
        private ?string $typeDefinitionType = null,
        private ?Indent $indent = null,
        private ?Quotes $quotes = null,
        private ?array $sortStrategies = null,
        private ?string $fileNameStrategy = null,
    ) {
    }

    public function getInputDir(): ?string
    {
        return $this->inputDir;
    }

    public function setInputDir(?string $inputDir): self
    {
        $this->inputDir = $inputDir;

        return $this;
    }

    public function getOutputDir(): ?string
    {
        return $this->outputDir;
    }

    public function setOutputDir(?string $outputDir): self
    {
        $this->outputDir = $outputDir;

        return $this;
    }

    /**
     * @phpstan-return ?FileType::TYPE_*
     */
    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    /**
     * @phpstan-param ?FileType::TYPE_* $fileType
     */
    public function setFileType(?string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * @phpstan-return ?TypeDefinitionType::TYPE_*
     */
    public function getTypeDefinitionType(): ?string
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

    public function getIndent(): ?Indent
    {
        return $this->indent;
    }

    public function setIndent(?Indent $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    public function getQuotes(): ?Quotes
    {
        return $this->quotes;
    }

    public function setQuotes(?Quotes $quotes): self
    {
        $this->quotes = $quotes;

        return $this;
    }

    /**
     * @return ?class-string<SortStrategy>[]
     */
    public function getSortStrategies(): ?array
    {
        return $this->sortStrategies;
    }

    /**
     * @param ?class-string<SortStrategy>[] $sortStrategies
     */
    public function setSortStrategies(?array $sortStrategies): self
    {
        $this->sortStrategies = $sortStrategies;

        return $this;
    }

    /**
     * @return ?class-string<FileNameStrategy>
     */
    public function getFileNameStrategy(): ?string
    {
        return $this->fileNameStrategy;
    }

    /**
     * @param ?class-string<FileNameStrategy> $fileNameStrategy
     */
    public function setFileNameStrategy(?string $fileNameStrategy): self
    {
        $this->fileNameStrategy = $fileNameStrategy;

        return $this;
    }

    /**
     * @phpstan-param ConfigArray $array
     */
    public static function fromArray(array $array): self
    {
        $fileType = isset($array[C::FILE_TYPE_KEY])
            ? Assert::inStringArrayNullable(
                $array[C::FILE_TYPE_KEY],
                C::FILE_TYPE_VALID_VALUES,
            )
            : null;

        $typeDefinitionType = isset($array[C::TYPE_DEFINITION_TYPE_KEY])
            ? Assert::inStringArrayNullable(
                $array[C::TYPE_DEFINITION_TYPE_KEY],
                C::TYPE_DEFINITION_TYPE_VALID_VALUES,
            )
            : null;

        $indentStyle = isset($array[C::INDENT_KEY][C::INDENT_STYLE_KEY])
            ? Assert::inStringArrayNullable(
                $array[C::INDENT_KEY][C::INDENT_STYLE_KEY],
                C::INDENT_STYLE_VALID_VALUES,
            )
            : null;

        $quotes = isset($array[C::QUOTES_KEY])
            ? Assert::inStringArrayNullable(
                $array[C::QUOTES_KEY],
                C::QUOTES_VALID_VALUES,
            )
            : null;

        $sortStrategies = isset($array[C::SORT_STRATEGIES_KEY])
            ? Assert::interfaceClassStringArrayNullable(
                $array[C::SORT_STRATEGIES_KEY],
                SortStrategy::class,
            )
            : null;

        $fileNameStrategy = isset($array[C::FILE_NAME_STRATEGY_KEY])
            ? Assert::interfaceClassStringNullable(
                $array[C::FILE_NAME_STRATEGY_KEY],
                FileNameStrategy::class,
            )
            : null;

        return new self(
            inputDir: $array[C::INPUT_DIR_KEY] ?? null,
            outputDir: $array[C::OUTPUT_DIR_KEY] ?? null,
            fileType: $fileType,
            typeDefinitionType: $typeDefinitionType,
            indent: isset($array[C::INDENT_KEY]) ? new Indent(
                style: $indentStyle ?? C::INDENT_STYLE_DEFAULT,
                count: $array[C::INDENT_KEY][C::INDENT_COUNT_KEY] ?? C::INDENT_COUNT_DEFAULT,
            ) : null,
            quotes: $quotes ? new Quotes($quotes) : null,
            sortStrategies: $sortStrategies,
            fileNameStrategy: $fileNameStrategy,
        );
    }
}
