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
                ->scalarNode('bot_url')
                    ->defaultValue('http://idn.owja.de')
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('enable_sites')
                    ->defaultFalse()
                ->end()
                ->scalarNode('remote_token')
                    ->defaultNull()
                ->end()
                ->integerNode('remote_timeout')
                    ->defaultValue(10)
                    ->min(5)
                    ->max(60)
                ->end()
                ->scalarNode('default_url')
                    ->defaultNull()
                ->end()
                ->integerNode('limit_width')
                    ->defaultValue(1920)
                    ->min(0)
                    ->max(1920 * 8)
                ->end()
                ->integerNode('limit_height')
                    ->defaultValue(1080)
                    ->min(0)
                    ->max(1080 * 8)
                ->end()
                ->booleanNode('enable_compression')
                    ->defaultFalse()
                ->end()
                ->scalarNode('temp_dir')
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
