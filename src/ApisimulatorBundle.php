<?php

namespace Aleksanthaar\ApisimulatorBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApisimulatorBundle extends Bundle
{
    public const BUNDLE_KEY = 'apisimulator';

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}