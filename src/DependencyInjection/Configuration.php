<?php

namespace Brainshaker95\PhpToTsBundle\DependencyInjection;

use Brainshaker95\PhpToTsBundle\Interface\Config;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\Indent;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('php_to_ts');

        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('input_dir')
                    ->info('Directory in which to look for models to include')
                    ->defaultValue(Config::DEFAULT_INPUT_DIR)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('output_dir')
                    ->info('Directory in which to dump generated TypeScript interfaces')
                    ->defaultValue(Config::DEFAULT_OUTPUT_DIR)
                    ->cannotBeEmpty()
                ->end()
                ->enumNode('file_type')
                    ->info('File type to use for TypeScript interfaces')
                    ->defaultValue(Config::DEFAULT_FILE_TYPE)
                    ->values([FileType::TYPE_DECLARATION, FileType::TYPE_MODULE])
                ->end()
                ->arrayNode('indent')
                    ->info('Indentation used for generated TypeScript interfaces')
                    ->children()
                        ->enumNode('style')
                            ->info('Indent style used for TypeScript interfaces')
                            ->defaultValue(Config::DEFAULT_INDENT_STYLE)
                            ->values([Indent::STYLE_SPACE, Indent::STYLE_TAB])
                        ->end()
                        ->integerNode('count')
                            ->info('Number of indent style characters per indent')
                            ->defaultValue(Config::DEFAULT_INDENT_COUNT)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sort_strategies')
                    ->info('Class names of sort strategies used for TypeScript properties')
                    ->defaultValue(Config::DEFAULT_SORT_STRATEGIES)
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()
                ->end()
                ->scalarNode('file_name_strategy')
                    ->info('Class name of file name strategies used for generated TypeScript files')
                    ->defaultValue(Config::DEFAULT_FILE_NAME_STRATEGY)
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
