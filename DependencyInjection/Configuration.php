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
                ->scalarNode('strict')->defaultValue(true)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
