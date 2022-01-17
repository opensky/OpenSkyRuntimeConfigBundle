<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OpenSkyRuntimeConfigExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $container->setAlias('opensky.runtime_config.provider', $config['provider']);

        if ($config['cascade']) {
            $container->getDefinition('opensky.runtime_config.parameter_bag')->addMethodCall('setContainer', [new Reference('service_container')]);
        }

        $container->setParameter('opensky.runtime_config.logger.level', $config['logging']['level']);

        if ($config['logging']['enabled']) {
            $container->getDefinition('opensky.runtime_config.parameter_bag')->addArgument(new Reference('opensky.runtime_config.logger'));
        }

        // always autowire the runtime parameter bag, require Symfony >= 2.8, < 3.3
        if (!method_exists('Symfony\Component\DependencyInjection\ContainerBuilder', 'fileExists') && method_exists('Symfony\Component\DependencyInjection\Definition', 'addAutowiringType')) {
            $container->getDefinition('opensky.runtime_config.parameter_bag')->addAutowiringType('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'opensky_runtime_config';
    }
}
