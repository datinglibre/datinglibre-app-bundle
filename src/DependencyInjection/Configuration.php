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
                ->arrayNode('rules')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('payment_providers')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('url')->end()
                            ->booleanNode('active')->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('image_upload')->end()
                ->booleanNode('is_demo')->end()
                ->scalarNode('admin_email')->end()
                ->scalarNode('images_bucket')->end()
                ->scalarNode('site_name')->end()
                ->scalarNode('site_description')->end()
            ->end();

        return $treeBuilder;
    }
}
