<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class DatingLibreAppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.xml');

        $configuration = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('datinglibre.categories', $configuration['categories']);
        $container->setParameter('datinglibre.attributes', $configuration['attributes']);
        $container->setParameter('datinglibre.block_reasons', $configuration['block_reasons']);
        $container->setParameter('datinglibre.image_upload', $configuration['image_upload']);
        $container->setParameter('datinglibre.is_demo', $configuration['is_demo']);
        $container->setParameter('datinglibre.admin_email', $configuration['admin_email']);
        $container->setParameter('datinglibre.images_bucket', $configuration['images_bucket']);
    }
}