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
        $this->nodeBuilder->scalarNode(Config::INPUT_DIR_KEY)
            ->info(Config::INPUT_DIR_DESC)
            ->defaultValue(Config::INPUT_DIR_DEFAULT)
            ->cannotBeEmpty()
        ;

        return $this;
    }

    private function outputDir(): self
    {
        $this->nodeBuilder->scalarNode(Config::OUTPUT_DIR_KEY)
            ->info(Config::OUTPUT_DIR_DESC)
            ->defaultValue(Config::OUTPUT_DIR_DEFAULT)
            ->cannotBeEmpty()
        ;

        return $this;
    }

    private function fileType(): self
    {
        $this->nodeBuilder->enumNode(Config::FILE_TYPE_KEY)
            ->info(Config::FILE_TYPE_DESC)
            ->defaultValue(Config::FILE_TYPE_DEFAULT)
            ->values([FileType::TYPE_DECLARATION, FileType::TYPE_MODULE])
        ;

        return $this;
    }

    private function indent(): self
    {
        $indent = $this->nodeBuilder->arrayNode(Config::INDENT_KEY)
            ->info(Config::INDENT_DESC)
            ->addDefaultsIfNotSet()
            ->children()
        ;

        $indent->enumNode(Config::INDENT_STYLE_KEY)
            ->info(Config::INDENT_STYLE_DESC)
            ->defaultValue(Config::INDENT_STYLE_DEFAULT)
            ->values([Indent::STYLE_SPACE, Indent::STYLE_TAB])
        ;

        $indent->integerNode(Config::INDENT_COUNT_KEY)
            ->info(Config::INDENT_COUNT_DESC)
            ->defaultValue(Config::INDENT_COUNT_DEFAULT)
        ;

        return $this;
    }

    private function sortStrategies(): self
    {
        $this->nodeBuilder->arrayNode(Config::SORT_STRATEGIES_KEY)
            ->info(Config::SORT_STRATEGIES_DESC)
            ->defaultValue(Config::SORT_STRATEGIES_DEFAULT)
            ->requiresAtLeastOneElement()
            ->scalarPrototype()
        ;

        return $this;
    }

    private function fileNameStrategies(): self
    {
        $this->nodeBuilder->scalarNode(Config::FILE_NAME_STRATEGY_KEY)
            ->info(Config::FILE_NAME_STRATEGY_DESC)
            ->defaultValue(Config::FILE_NAME_STRATEGY_DEFAULT)
            ->cannotBeEmpty()
        ;

        return $this;
    }
}
