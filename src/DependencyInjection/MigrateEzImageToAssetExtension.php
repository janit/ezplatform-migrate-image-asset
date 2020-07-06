<?php

namespace Janit\MigrateEzImageToAssetBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MigrateEzImageToAssetExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');
        $container->addResource(new FileResource(__DIR__ . '/../Resources/config/services.yaml'));

    }

}