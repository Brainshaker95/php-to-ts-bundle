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
use Brainshaker95\PhpToTsBundle\Model\Config\TypeDefinitionType;

/**
 * @phpstan-type ConfigArray array{
 *     input_dir?: ?string,
 *     output_dir?: ?string,
 *     file_type?: ?string,
 *     type_definition_type?: ?string,
 *     indent?: ?array{
 *         style: ?string,
 *         count: ?int<0,max>,
 *     },
 *     quotes?: ?string,
 *     sort_strategies?: ?non-empty-string[],
 *     file_name_strategy?: ?string,
 * }
 */
interface Config
{
    final public const OUTPUT_DIR_KEY     = 'output_dir';
    final public const OUTPUT_DIR_DEFAULT = 'assets/ts/types/php-to-ts';
    final public const OUTPUT_DIR_DESC    = 'Directory in which to dump TypeScript interfaces';

    final public const INPUT_DIR_KEY     = 'input_dir';
    final public const INPUT_DIR_DEFAULT = 'src/Model';
    final public const INPUT_DIR_DESC    = 'Directory in which to look for models to include';

    final public const FILE_TYPE_KEY          = 'file_type';
    final public const FILE_TYPE_DEFAULT      = FileType::TYPE_MODULE;
    final public const FILE_TYPE_DESC         = 'File type to use for TypeScript interfaces';
    final public const FILE_TYPE_VALID_VALUES = [FileType::TYPE_DECLARATION, FileType::TYPE_MODULE];

    final public const TYPE_DEFINITION_TYPE_KEY          = 'type_definition_type';
    final public const TYPE_DEFINITION_TYPE_DEFAULT      = TypeDefinitionType::TYPE_INTERFACE;
    final public const TYPE_DEFINITION_TYPE_DESC         = 'Type definition type to use for TypeScript interfaces';
    final public const TYPE_DEFINITION_TYPE_VALID_VALUES = [TypeDefinitionType::TYPE_INTERFACE, TypeDefinitionType::TYPE_TYPE_ALIAS];

    final public const INDENT_KEY                = 'indent';
    final public const INDENT_DESC               = 'Indentation used for TypeScript interfaces';
    final public const INDENT_STYLE_KEY          = 'style';
    final public const INDENT_STYLE_DEFAULT      = Indent::STYLE_SPACE;
    final public const INDENT_STYLE_DESC         = 'Indent style used for TypeScript interfaces';
    final public const INDENT_STYLE_VALID_VALUES = [Indent::STYLE_SPACE, Indent::STYLE_TAB];
    final public const INDENT_COUNT_KEY          = 'count';
    final public const INDENT_COUNT_DEFAULT      = 2;
    final public const INDENT_COUNT_DESC         = 'Number of indent style characters per indent';

    final public const QUOTES_KEY          = 'quotes';
    final public const QUOTES_DESC         = 'Quote style used for strings in TypeScript interfaces';
    final public const QUOTES_DEFAULT      = Quotes::STYLE_SINGLE;
    final public const QUOTES_VALID_VALUES = [Quotes::STYLE_DOUBLE, Quotes::STYLE_SINGLE];

    final public const SORT_STRATEGIES_KEY     = 'sort_strategies';
    final public const SORT_STRATEGIES_DEFAULT = [
        AlphabeticalAsc::class,
        ConstructorFirst::class,
        ReadonlyFirst::class,
    ];
    final public const SORT_STRATEGIES_DESC = 'Class names of sort strategies used for TypeScript properties';

    final public const FILE_NAME_STRATEGY_KEY     = 'file_name_strategy';
    final public const FILE_NAME_STRATEGY_DEFAULT = KebabCase::class;
    final public const FILE_NAME_STRATEGY_DESC    = 'Class name of file name strategies used for TypeScript files';

    public function getInputDir(): ?string;

    public function setInputDir(string $inputDir): self;

    public function getOutputDir(): ?string;

    public function setOutputDir(string $outputDir): self;

    /**
     * @phpstan-return ?FileType::TYPE_*
     */
    public function getFileType(): ?string;

    /**
     * @phpstan-param FileType::TYPE_* $fileType
     */
    public function setFileType(string $fileType): self;

    /**
     * @phpstan-return ?TypeDefinitionType::TYPE_*
     */
    public function getTypeDefinitionType(): ?string;

    /**
     * @phpstan-param TypeDefinitionType::TYPE_* $typeDefinitionType
     */
    public function setTypeDefinitionType(string $typeDefinitionType): self;

    public function getIndent(): ?Indent;

    public function setIndent(Indent $indent): self;

    public function getQuotes(): ?Quotes;

    public function setQuotes(Quotes $quotes): self;

    /**
     * @return ?class-string<SortStrategy>[]
     */
    public function getSortStrategies(): ?array;

    /**
     * @param class-string<SortStrategy>[] $sortStrategies
     */
    public function setSortStrategies(array $sortStrategies): self;

    /**
     * @return ?class-string<FileNameStrategy>
     */
    public function getFileNameStrategy(): ?string;

    /**
     * @param class-string<FileNameStrategy> $fileNameStrategy
     */
    public function setFileNameStrategy(string $fileNameStrategy): self;

    /**
     * @phpstan-param ConfigArray $array
     */
    public static function fromArray(array $array): self;
}
