<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * @see Symfony\Component\Config\Definition\ConfigurationInterface::getConfigTreeBuilder()
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('opensky_runtime_config');

        $rootNode
            ->children()
                ->scalarNode('provider')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('cascade')->defaultTrue()->end()
                ->arrayNode('logging')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('enabled')->defaultTrue()->end()
                        ->scalarNode('level')
                            ->defaultValue('debug')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function($v){ return strtolower($v); })
                            ->end()
                            ->validate()
                                ->ifNotInArray(get_class_methods('Symfony\Component\HttpKernel\Log\LoggerInterface'))
                                ->thenInvalid('The "%s" level does not correspond to a method in LoggerInterface')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
