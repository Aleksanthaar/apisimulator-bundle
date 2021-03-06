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
        // var_dump($configs[0]['warnings']);die();

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        if (!empty($config['guards'])) {
            $container->setParameter('apisimulator.guards.methods', $config['guards']['methods']);
            $container->setParameter('apisimulator.guards.status_codes', $config['guards']['status_codes']);
            $container->setParameter('apisimulator.guards.paths', $config['guards']['paths']);
        } else {
            // Ugly, refine later
            $container->setParameter('apisimulator.guards.methods', []);
            $container->setParameter('apisimulator.guards.status_codes', []);
            $container->setParameter('apisimulator.guards.paths', []);
        }

        if (!empty($config['headers_removal'])) {
            $container->setParameter('apisimulator.headers_removal.request', $config['headers_removal']['request']);
            $container->setParameter('apisimulator.headers_removal.response', $config['headers_removal']['response']);
        } else {
            // Ugly, refine later
            $container->setParameter('apisimulator.headers_removal.request', []);
            $container->setParameter('apisimulator.headers_removal.response', []);
        }

        if (!empty($config['warnings'])) {
            $container->setParameter('apisimulator.warnings.request_headers', $config['warnings']['request_headers']);
            $container->setParameter('apisimulator.warnings.request_body', $config['warnings']['request_body']);
        } else {
            // Ugly, refine later
            $container->setParameter('apisimulator.warnings.request_headers', []);
            $container->setParameter('apisimulator.warnings.request_body', []);
        }

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));

        $loader->load('services.yaml');
    }
}