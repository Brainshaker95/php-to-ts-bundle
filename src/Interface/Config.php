<?php

namespace Brainshaker95\PhpToTsBundle\Interface;

use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\KebabCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\AlphabeticalAsc;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\ConstructorFirst;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\ReadonlyFirst;

interface Config
{
    public const DEFAULT_FILE_TYPE          = FileType::TYPE_MODULE;
    public const DEFAULT_INDENT_COUNT       = 2;
    public const DEFAULT_INDENT_STYLE       = Indent::STYLE_SPACE;
    public const DEFAULT_INPUT_DIR          = 'src/Model/TypeScriptables';
    public const DEFAULT_OUTPUT_DIR         = 'assets/ts/types/php-to-ts';
    public const DEFAULT_FILE_NAME_STRATEGY = KebabCase::class;

    public const DEFAULT_SORT_STRATEGIES = [
        AlphabeticalAsc::class,
        ConstructorFirst::class,
        ReadonlyFirst::class,
    ];

    public function getInputDir(): ?string;

    public function getOutputDir(): ?string;

    /**
     * @phpstan-return FileType::TYPE_*
     */
    public function getFileType(): ?string;

    public function getIndent(): ?Indent;

    /**
     * @return class-string<SortStrategy>[]
     */
    public function getSortStrategies(): ?array;

    /**
     * @return class-string<FileNameStrategy>
     */
    public function getFileNameStrategy(): ?string;
}
