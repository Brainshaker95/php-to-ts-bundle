<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class PhpToTsExtension extends Extension
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);

        if (!$configuration instanceof ConfigurationInterface) {
            return;
        }

        $container->setParameter('php_to_ts', $this->processConfiguration($configuration, $configs));
    }
}
