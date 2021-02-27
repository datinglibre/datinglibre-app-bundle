<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dating_libre');

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('categories')
            ->scalarPrototype()->end()
            ->end()
            ->arrayNode('attributes')
            ->arrayPrototype()
            ->scalarPrototype()->end()
            ->end()
            ->end()
            ->arrayNode('block_reasons')
            ->scalarPrototype()->end()
            ->end()
            ->booleanNode('image_upload')->end()
            ->booleanNode('is_demo')->end()
            ->scalarNode('admin_email')->end()
            ->scalarNode('images_bucket')->end()
            ->end();

        return $treeBuilder;
    }
}
