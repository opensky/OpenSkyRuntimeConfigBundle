<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OpenSkyRuntimeConfigExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('service.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $container->setAlias('opensky.runtime_config.provider', $config['provider']);
        $container->setParameter('opensky.runtime_config.strict', $config['strict']);
        $container->setParameter('opensky.runtime_config.logger.level', $config['logging']['level']);

        if ($config['logging']['enabled']) {
            $container->getDefinition('opensky.runtime_config')->addArgument(new Reference('opensky.runtime_config.logger'));
        }
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::getAlias()
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return 'opensky_runtime_config';
    }
}
