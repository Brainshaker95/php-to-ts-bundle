<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\DependencyInjection;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
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
        $this->nodeBuilder->scalarNode(C::INPUT_DIR_KEY)
            ->info(C::INPUT_DIR_DESC)
            ->defaultValue(C::INPUT_DIR_DEFAULT)
            ->cannotBeEmpty()
        ;

        return $this;
    }

    private function outputDir(): self
    {
        $this->nodeBuilder->scalarNode(C::OUTPUT_DIR_KEY)
            ->info(C::OUTPUT_DIR_DESC)
            ->defaultValue(C::OUTPUT_DIR_DEFAULT)
            ->cannotBeEmpty()
        ;

        return $this;
    }

    private function fileType(): self
    {
        $this->nodeBuilder->enumNode(C::FILE_TYPE_KEY)
            ->info(C::FILE_TYPE_DESC)
            ->defaultValue(C::FILE_TYPE_DEFAULT)
            ->values(C::FILE_TYPE_VALID_VALUES)
        ;

        return $this;
    }

    private function indent(): self
    {
        $indent = $this->nodeBuilder->arrayNode(C::INDENT_KEY)
            ->info(C::INDENT_DESC)
            ->addDefaultsIfNotSet()
            ->children()
        ;

        $indent->enumNode(C::INDENT_STYLE_KEY)
            ->info(C::INDENT_STYLE_DESC)
            ->defaultValue(C::INDENT_STYLE_DEFAULT)
            ->values(C::INDENT_STYLE_VALID_VALUES)
        ;

        $indent->integerNode(C::INDENT_COUNT_KEY)
            ->info(C::INDENT_COUNT_DESC)
            ->defaultValue(C::INDENT_COUNT_DEFAULT)
            ->min(0)
        ;

        return $this;
    }

    private function sortStrategies(): self
    {
        $this->nodeBuilder->arrayNode(C::SORT_STRATEGIES_KEY)
            ->info(C::SORT_STRATEGIES_DESC)
            ->defaultValue(C::SORT_STRATEGIES_DEFAULT)
            ->requiresAtLeastOneElement()
            ->scalarPrototype()
        ;

        return $this;
    }

    private function fileNameStrategies(): self
    {
        $this->nodeBuilder->scalarNode(C::FILE_NAME_STRATEGY_KEY)
            ->info(C::FILE_NAME_STRATEGY_DESC)
            ->defaultValue(C::FILE_NAME_STRATEGY_DEFAULT)
            ->cannotBeEmpty()
        ;

        return $this;
    }
}
