<?php

namespace Brainshaker95\PhpToTsBundle\Interface;

use Brainshaker95\PhpToTsBundle\Model\Config\FileType as ConfigFileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent as ConfigIndent;
use Brainshaker95\PhpToTsBundle\SortStrategy\AlphabeticalAsc;
use Brainshaker95\PhpToTsBundle\SortStrategy\ConstructorFirst;
use Brainshaker95\PhpToTsBundle\SortStrategy\ReadonlyFirst;

interface Config
{
    public const DEFAULT_FILE_TYPE          = ConfigFileType::TYPE_MODULE;
    public const DEFAULT_INDENT_COUNT       = 2;
    public const DEFAULT_INDENT_STYLE       = ConfigIndent::STYLE_SPACE;
    public const DEFAULT_INPUT_DIR          = 'src/Model/TypeScriptables';
    public const DEFAULT_OUTPUT_DIR         = 'resources/ts/types/generated';
    public const DEFAULT_FILE_NAME_STRATEGY = 'TODO';

    public const DEFAULT_SORT_STRATEGIES = [
        AlphabeticalAsc::class,
        ConstructorFirst::class,
        ReadonlyFirst::class,
    ];

    public function getInputDir(): ?string;

    public function getOutputDir(): ?string;

    /**
     * @phpstan-return ConfigFileType::TYPE_*
     */
    public function getFileType(): ?string;

    public function getIndent(): ?ConfigIndent;

    /**
     * @return class-string<SortStrategy>[]
     */
    public function getSortStrategies(): ?array;

    /**
     * @return class-string<FileNameStrategy>
     */
    public function getFileNameStrategy(): ?string;
}
