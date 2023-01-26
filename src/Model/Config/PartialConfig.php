<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Config;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Tool\Assert;

class PartialConfig implements C
{
    /**
     * @phpstan-param ?FileType::TYPE_* $fileType
     *
     * @param ?class-string<SortStrategy>[] $sortStrategies
     * @param ?class-string<FileNameStrategy> $fileNameStrategy
     */
    public function __construct(
        private ?string $inputDir = null,
        private ?string $outputDir = null,
        private ?string $fileType = null,
        private ?Indent $indent = null,
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

    public function getIndent(): ?Indent
    {
        return $this->indent;
    }

    public function setIndent(?Indent $indent): self
    {
        $this->indent = $indent;

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
     * @param array{
     *     input_dir?: ?string,
     *     output_dir?: ?string,
     *     file_type?: ?string,
     *     indent?: ?array{
     *         style: ?string,
     *         count: ?int<0,max>,
     *     },
     *     sort_strategies?: ?non-empty-string[],
     *     file_name_strategy?: ?string,
     * } $values
     */
    public static function fromArray(array $values): self
    {
        $fileType = isset($values[C::FILE_TYPE_KEY])
            ? Assert::inStringArrayNullable(
                $values[C::FILE_TYPE_KEY],
                C::FILE_TYPE_VALID_VALUES,
            )
            : null;

        $indentStyle = isset($values[C::INDENT_KEY][C::INDENT_STYLE_KEY])
            ? Assert::inStringArrayNullable(
                $values[C::INDENT_KEY][C::INDENT_STYLE_KEY],
                C::INDENT_STYLE_VALID_VALUES,
            )
            : null;

        $sortStrategies = isset($values[C::SORT_STRATEGIES_KEY])
            ? Assert::interfaceClassStringArrayNullable(
                $values[C::SORT_STRATEGIES_KEY],
                SortStrategy::class,
            )
            : null;

        $fileNameStrategy = isset($values[C::FILE_NAME_STRATEGY_KEY])
            ? Assert::interfaceClassStringNullable(
                $values[C::FILE_NAME_STRATEGY_KEY],
                FileNameStrategy::class,
            )
            : null;

        return new self(
            inputDir: $values[C::INPUT_DIR_KEY] ?? null,
            outputDir: $values[C::OUTPUT_DIR_KEY] ?? null,
            fileType: $fileType,
            indent: isset($values[C::INDENT_KEY]) ? new Indent(
                style: $indentStyle ?? C::INDENT_STYLE_DEFAULT,
                count: $values[C::INDENT_KEY][C::INDENT_COUNT_KEY] ?? C::INDENT_COUNT_DEFAULT,
            ) : null,
            sortStrategies: $sortStrategies,
            fileNameStrategy: $fileNameStrategy,
        );
    }
}
