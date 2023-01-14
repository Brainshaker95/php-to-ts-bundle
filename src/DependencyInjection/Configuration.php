<?php

namespace Brainshaker95\PhpToTsBundle\DependencyInjection;

use Brainshaker95\PhpToTsBundle\Interface\Config;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const TREE_BUILDER_NAME = 'php_to_ts';
    public const TREE_BUILDER_TYPE = 'array';

    private NodeBuilder $nodeBuilder;

    private TreeBuilder $treeBuilder;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $this->treeBuilder = new TreeBuilder(self::TREE_BUILDER_NAME, self::TREE_BUILDER_TYPE);
        $this->nodeBuilder = $this->rootNode()->children();

        $this
            ->inputDir()
            ->outputDir()
            ->fileType()
            ->indent()
            ->sortStrategies()
            ->fileNameStrategies()
        ;

        return $this->treeBuilder;
    }

    private function rootNode(): ArrayNodeDefinition
    {
        /**
         * @var ArrayNodeDefinition
         */
        return $this->treeBuilder->getRootNode();
    }

    private function inputDir(): self
    {
        $this->nodeBuilder->scalarNode('input_dir')
            ->info('Directory in which to look for models to include')
            ->defaultValue(Config::DEFAULT_INPUT_DIR)
            ->cannotBeEmpty()
        ;

        return $this;
    }

    private function outputDir(): self
    {
        $this->nodeBuilder->scalarNode('output_dir')
            ->info('Directory in which to dump generated TypeScript interfaces')
            ->defaultValue(Config::DEFAULT_OUTPUT_DIR)
            ->cannotBeEmpty()
        ;

        return $this;
    }

    private function fileType(): self
    {
        $this->nodeBuilder->enumNode('file_type')
            ->info('File type to use for TypeScript interfaces')
            ->defaultValue(Config::DEFAULT_FILE_TYPE)
            ->values([FileType::TYPE_DECLARATION, FileType::TYPE_MODULE])
        ;

        return $this;
    }

    private function indent(): self
    {
        $indent = $this->nodeBuilder->arrayNode('indent')
            ->info('Indentation used for generated TypeScript interfaces')
            ->addDefaultsIfNotSet()
            ->children()
        ;

        $indent->enumNode('style')
            ->info('Indent style used for TypeScript interfaces')
            ->defaultValue(Config::DEFAULT_INDENT_STYLE)
            ->values([Indent::STYLE_SPACE, Indent::STYLE_TAB])
        ;

        $indent->integerNode('count')
            ->info('Number of indent style characters per indent')
            ->defaultValue(Config::DEFAULT_INDENT_COUNT)
        ;

        return $this;
    }

    private function sortStrategies(): self
    {
        $this->nodeBuilder->arrayNode('sort_strategies')
            ->info('Class names of sort strategies used for TypeScript properties')
            ->defaultValue(Config::DEFAULT_SORT_STRATEGIES)
            ->requiresAtLeastOneElement()
            ->scalarPrototype()
        ;

        return $this;
    }

    private function fileNameStrategies(): self
    {
        $this->nodeBuilder->scalarNode('file_name_strategy')
            ->info('Class name of file name strategies used for generated TypeScript files')
            ->defaultValue(Config::DEFAULT_FILE_NAME_STRATEGY)
            ->cannotBeEmpty()
        ;

        return $this;
    }
}
