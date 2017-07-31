<?php

namespace Owja\ImageProxyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('owja_image_proxy');

        $rootNode
            ->children()
                ->arrayNode('remote')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('token')->defaultNull()->end()
                        ->integerNode('timeout')->defaultValue(10)->min(5)->max(60)->end()
                    ->end()
                ->end()
                ->scalarNode('temp_dir')->defaultValue('%kernel.root_dir%/../var/temp/')->end()
                ->scalarNode('cache_service')->defaultValue('owja_image_proxy.cache')->end()
                ->booleanNode('optimization')->defaultTrue()->end()
                ->arrayNode('limits')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('width')
                            ->defaultValue(1920)
                            ->min(0)
                            ->max(1920 * 8)
                        ->end()
                        ->integerNode('height')
                            ->defaultValue(1080)
                            ->min(0)
                            ->max(1080 * 8)
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('default_site')->defaultValue('default')->end()
                ->booleanNode('enable_sites')->defaultFalse()->end()
                ->booleanNode('enable_dynamic')->defaultFalse()->end()
                ->booleanNode('enable_presets')->defaultTrue()->end()
                ->arrayNode('sites')
                    ->useAttributeAsKey('code')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->defaultNull()->end()
                            ->scalarNode('url')
                                ->isRequired()
                            ->end()
                            ->arrayNode('presets')
                                ->useAttributeAsKey('code')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')->defaultNull()->end()
                                        ->enumNode('type')
                                            ->values([ 'resize', 'crop' ])
                                            ->defaultValue('resize')
                                        ->end()
                                        ->integerNode('width')
                                            ->defaultNull()
                                            ->min(0)
                                            ->max(1920 * 8)
                                        ->end()
                                        ->integerNode('height')
                                            ->defaultNull()
                                            ->min(0)
                                            ->max(1080 * 8)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('presets')
                    ->useAttributeAsKey('code')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->defaultNull()->end()
                            ->integerNode('width')
                                ->defaultNull()
                                ->min(0)
                                ->max(1920 * 8)
                            ->end()
                            ->integerNode('height')
                                ->defaultNull()
                                ->min(0)
                                ->max(1080 * 8)
                            ->end()
                            ->enumNode('type')
                                ->values([ 'resize', 'crop' ])
                                ->defaultValue('resize')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
