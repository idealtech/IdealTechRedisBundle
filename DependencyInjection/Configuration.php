<?php

/*
 * This file is part of the IdealTech Redis bundle.
 *
 * (c) Ideal Technology <http://www.idealtechnology.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IdealTech\RedisBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
    Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * The Redis bundle configuration class
 *
 * @author Guillaume Coguiec <gcoguiec@idealtechnology.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     *
     * @return TreeBuilder $treeBuilder The configuration tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ideal_tech_redis');

        $rootNode
            ->children()
                ->scalarNode('default_client')
                    ->cannotBeEmpty()
                    ->defaultValue('default')
                ->end()
                ->arrayNode('clients')
                    ->requiresAtLeastOneElement()
                    ->defaultValue(array())
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('host')
                                ->cannotBeEmpty()
                                ->defaultValue('localhost')
                            ->end()
                            ->scalarNode('port')
                                ->defaultValue('6379')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}