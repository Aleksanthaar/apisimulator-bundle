<?php

namespace Aleksanthaar\ApisimulatorBundle\DependencyInjection;

use Aleksanthaar\ApisimulatorBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ApisimulatorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        if (!empty($config['guards'])) {
            $container->setParameter('apisimulator.guards.methods', $config['guards']['methods']);
            $container->setParameter('apisimulator.guards.status_codes', $config['guards']['status_codes']);
            $container->setParameter('apisimulator.guards.paths', $config['guards']['paths']);
        }

        if (!empty($config['headers_removal'])) {
            $container->setParameter('apisimulator.headers_removal.request', $config['headers_removal']['request']);
            $container->setParameter('apisimulator.headers_removal.response', $config['headers_removal']['response']);
        }

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));

        $loader->load('services.yaml');
    }
}