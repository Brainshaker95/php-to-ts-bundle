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
    public const FILE_NAME_STRATEGY_DEFAULT = KebabCase::class;
    public const FILE_NAME_STRATEGY_DESC    = 'Class name of file name strategies used for generated TypeScript files';

    public const FILE_TYPE_DEFAULT = FileType::TYPE_MODULE;
    public const FILE_TYPE_DESC    = 'File type to use for TypeScript interfaces';

    public const INDENT_DESC          = 'Indentation used for generated TypeScript interfaces';
    public const INDENT_COUNT_DEFAULT = 2;
    public const INDENT_COUNT_DESC    = 'Number of indent style characters per indent';
    public const INDENT_STYLE_DEFAULT = Indent::STYLE_SPACE;
    public const INDENT_STYLE_DESC    = 'Indent style used for TypeScript interfaces';

    public const INPUT_DIR_DEFAULT = 'src/Model/TypeScriptables';
    public const INPUT_DIR_DESC    = 'Directory in which to look for models to include';

    public const OUTPUT_DIR_DEFAULT = 'assets/ts/types/php-to-ts';
    public const OUTPUT_DIR_DESC    = 'Directory in which to dump generated TypeScript interfaces';

    public const SORT_STRATEGIES_DEFAULT = [
        AlphabeticalAsc::class,
        ConstructorFirst::class,
        ReadonlyFirst::class,
    ];
    public const SORT_STRATEGIES_DESC = 'Class names of sort strategies used for TypeScript properties';

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
