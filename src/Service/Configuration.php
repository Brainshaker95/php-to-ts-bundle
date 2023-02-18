<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service;

use Brainshaker95\PhpToTsBundle\Interface\Config;
use Brainshaker95\PhpToTsBundle\Interface\FileNameStrategy;
use Brainshaker95\PhpToTsBundle\Interface\SortStrategy;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\FullConfig;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Brainshaker95\PhpToTsBundle\Model\Config\TypeDefinitionType;

/**
 * @internal
 */
final class Configuration
{
    private FullConfig $config;

    /**
     * @param array{
     *     input_dir: string,
     *     output_dir: string,
     *     file_type: FileType::TYPE_*,
     *     type_definition_type: TypeDefinitionType::TYPE_*,
     *     indent: array{
     *         style: Indent::STYLE_*,
     *         count: int<0,max>,
     *     },
     *     quotes: Quotes::STYLE_*,
     *     sort_strategies: class-string<SortStrategy>[],
     *     file_name_strategy: class-string<FileNameStrategy>,
     * } $config
     */
    public function __construct(array $config)
    {
        $this->config = FullConfig::fromArray($config);
    }

    /**
     * Get the base configuration.
     */
    public function get(): FullConfig
    {
        return $this->config;
    }

    /**
     * Merge the base configuration with the given configuration.
     *
     * @param ?Config $config the config to merge
     */
    public function merge(?Config $config): FullConfig
    {
        if ($config instanceof FullConfig) {
            return $config;
        }

        if (!$config) {
            return $this->config;
        }

        return new FullConfig(
            inputDir: $config->getInputDir() ?? $this->config->getInputDir(),
            outputDir: $config->getOutputDir() ?? $this->config->getOutputDir(),
            fileType: $config->getFileType() ?? $this->config->getFileType(),
            typeDefinitionType: $config->getTypeDefinitionType() ?? $this->config->getTypeDefinitionType(),
            indent: $config->getIndent() ?? $this->config->getIndent(),
            quotes: $config->getQuotes() ?? $this->config->getQuotes(),
            sortStrategies: $config->getSortStrategies() ?? $this->config->getSortStrategies(),
            fileNameStrategy: $config->getFileNameStrategy() ?? $this->config->getFileNameStrategy(),
        );
    }
}
