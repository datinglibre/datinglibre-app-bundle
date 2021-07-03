<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DatingLibreAppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yaml');

        $configuration = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('datinglibre.categories', $configuration['categories']);
        $container->setParameter('datinglibre.attributes', $configuration['attributes']);
        $container->setParameter('datinglibre.rules', $configuration['rules']);
        $container->setParameter('datinglibre.image_upload', $configuration['image_upload']);
        $container->setParameter('datinglibre.is_demo', $configuration['is_demo']);
        $container->setParameter('datinglibre.admin_email', $configuration['admin_email']);
        $container->setParameter('datinglibre.images_bucket', $configuration['images_bucket']);
        $container->setParameter('datinglibre.site_name', $configuration['site_name']);
        $container->setParameter('datinglibre.site_description', $configuration['site_description']);
        $container->setParameter('datinglibre.payment_providers', $configuration['payment_providers']);
        $container->setParameter('datinglibre.interests', $configuration['interests']);
        $container->setParameter('datinglibre.testing_user_email_addresses', $configuration['testing_user_email_addresses']);
    }
}
