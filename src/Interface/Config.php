<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Interface;

use Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\KebabCase;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\AlphabeticalAsc;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\ConstructorFirst;
use Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\ReadonlyFirst;

interface Config
{
    public const OUTPUT_DIR_KEY     = 'output_dir';
    public const OUTPUT_DIR_DEFAULT = 'assets/ts/types/php-to-ts';
    public const OUTPUT_DIR_DESC    = 'Directory in which to dump generated TypeScript interfaces';

    public const INPUT_DIR_KEY     = 'input_dir';
    public const INPUT_DIR_DEFAULT = 'src/Model/TypeScriptables';
    public const INPUT_DIR_DESC    = 'Directory in which to look for models to include';

    public const FILE_TYPE_KEY          = 'file_type';
    public const FILE_TYPE_DEFAULT      = FileType::TYPE_MODULE;
    public const FILE_TYPE_DESC         = 'File type to use for TypeScript interfaces';
    public const FILE_TYPE_VALID_VALUES = [FileType::TYPE_DECLARATION, FileType::TYPE_MODULE];

    public const INDENT_KEY                = 'indent';
    public const INDENT_DESC               = 'Indentation used for generated TypeScript interfaces';
    public const INDENT_STYLE_KEY          = 'style';
    public const INDENT_STYLE_DEFAULT      = Indent::STYLE_SPACE;
    public const INDENT_STYLE_DESC         = 'Indent style used for TypeScript interfaces';
    public const INDENT_STYLE_VALID_VALUES = [Indent::STYLE_SPACE, Indent::STYLE_TAB];
    public const INDENT_COUNT_KEY          = 'count';
    public const INDENT_COUNT_DEFAULT      = 2;
    public const INDENT_COUNT_DESC         = 'Number of indent style characters per indent';

    public const QUOTES_KEY          = 'quotes';
    public const QUOTES_DESC         = 'Quote style used for strings in generated TypeScript interfaces';
    public const QUOTES_DEFAULT      = Quotes::STYLE_SINGLE;
    public const QUOTES_VALID_VALUES = [Quotes::STYLE_DOUBLE, Quotes::STYLE_SINGLE];

    public const SORT_STRATEGIES_KEY     = 'sort_strategies';
    public const SORT_STRATEGIES_DEFAULT = [
        AlphabeticalAsc::class,
        ConstructorFirst::class,
        ReadonlyFirst::class,
    ];
    public const SORT_STRATEGIES_DESC = 'Class names of sort strategies used for TypeScript properties';

    public const FILE_NAME_STRATEGY_KEY     = 'file_name_strategy';
    public const FILE_NAME_STRATEGY_DEFAULT = KebabCase::class;
    public const FILE_NAME_STRATEGY_DESC    = 'Class name of file name strategies used for generated TypeScript files';

    public function getInputDir(): ?string;

    public function getOutputDir(): ?string;

    /**
     * @phpstan-return FileType::TYPE_*
     */
    public function getFileType(): ?string;

    public function getIndent(): ?Indent;

    public function getQuotes(): ?Quotes;

    /**
     * @return class-string<SortStrategy>[]
     */
    public function getSortStrategies(): ?array;

    /**
     * @return class-string<FileNameStrategy>
     */
    public function getFileNameStrategy(): ?string;

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
    public static function fromArray(array $values): self;
}
