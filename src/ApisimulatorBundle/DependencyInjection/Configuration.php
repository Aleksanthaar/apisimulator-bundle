<?php

namespace Aleksanthaar\ApisimulatorBundle\DependencyInjection;

use Aleksanthaar\ApisimulatorBundle\ApisimulatorBundle;
use Suez\CoreBundle\SuezCoreBundle;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(ApisimulatorBundle::BUNDLE_KEY);
        $rootNode    = method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root(ApisimulatorBundle::BUNDLE_KEY);

        $rootNode
            ->children()
                ->arrayNode('guards')
                    ->children()
                        ->arrayNode('methods')
                            ->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('status_codes')
                            ->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('paths')
                            ->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('headers_removal')
                    ->children()
                        ->arrayNode('request')
                            ->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('response')
                            ->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('warnings')
                    ->children()
                        ->arrayNode('request_headers')
                            ->defaultValue([])
                            ->useAttributeAsKey('name')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('message')->defaultValue('')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('request_body')
                            ->defaultValue([])
                            ->useAttributeAsKey('name')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('message')->defaultValue('')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}