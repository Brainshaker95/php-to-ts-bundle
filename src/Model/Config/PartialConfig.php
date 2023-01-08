<?php

namespace Brainshaker95\PhpToTsBundle\Model\Config;

use Brainshaker95\PhpToTsBundle\Interface\Config;
use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;

class PartialConfig implements Config
{
    /**
     * @phpstan-param ?FileType::TYPE_* $fileType
     *
     * @param ?class-string<SortStrategy>[] $sortStrategies
     */
    public function __construct(
        private ?string $inputDir = null,
        private ?string $outputDir = null,
        private ?string $fileType = null,
        private ?Indent $indent = null,
        private ?array $sortStrategies = null,
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
}
