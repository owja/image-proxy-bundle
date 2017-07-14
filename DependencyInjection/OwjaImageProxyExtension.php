<?php

namespace Owja\ImageProxyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OwjaImageProxyExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);


        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->setConfigServiceDefinition($config, $container);
        $this->setProxyServiceDefinition($config, $container);
        $this->setProcessServiceDefinition($config, $container);
    }

    /**
     * Configure Config Service
     *
     * @param $config
     * @param ContainerBuilder $container
     */
    protected function setConfigServiceDefinition($config, ContainerBuilder $container)
    {
        $definition =$container->getDefinition('owja_image_proxy.config');
        $definition->setArguments(['$config' => $config]);

        $container->setDefinition('owja_image_proxy.config', $definition);
    }

    /**
     * Configure Proxy Service
     *
     * @param $config
     * @param ContainerBuilder $container
     */
    protected function setProxyServiceDefinition($config, ContainerBuilder $container)
    {
        $definition = $container->getDefinition('owja_image_proxy.proxy');

        $definition->setArguments([
            '$filesystem' => new Reference($config['cache_service']),
            '$processor' => new Reference('owja_image_proxy.process'),
            '$timeout' => $config['remote']['timeout'],
        ]);

        $container->setDefinition('owja_image_proxy.proxy', $definition);
    }

    /**
     * Configure Process Service
     *
     * @param $config
     * @param ContainerBuilder $container
     */
    protected function setProcessServiceDefinition($config, ContainerBuilder $container)
    {
        $definition = $container->getDefinition('owja_image_proxy.process');
        $definition->setArguments([
            '$temp' => $config['temp_dir'],
            '$optimization' => $config['optimization'],
        ]);

        $container->setDefinition('owja_image_proxy.process', $definition);
    }
}
