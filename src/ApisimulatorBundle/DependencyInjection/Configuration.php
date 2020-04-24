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

        // Later

        return $treeBuilder;
    }
}