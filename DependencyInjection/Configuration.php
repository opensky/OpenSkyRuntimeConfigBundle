<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection;

use OpenSky\Bundle\RuntimeConfigBundle\Util\LogUtil;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('opensky_runtime_config');

        $rootNode
            ->children()
                ->scalarNode('provider')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cascade')
                    ->defaultTrue()
                ->end()
                ->arrayNode('logging')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('enabled')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('level')
                            ->defaultValue('debug')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function($v){ return strtolower($v); })
                            ->end()
                            ->validate()
                                ->ifNotInArray(LogUtil::getValidLogLevels())
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
