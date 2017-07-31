<?php
declare(strict_types=1);

namespace Owja\ImageProxyBundle\test;

use Owja\ImageProxyBundle\Exception\ConfigurationException;
use Owja\ImageProxyBundle\Exception\NotFoundException;
use Owja\ImageProxyBundle\Service\Config;
use PHPUnit\Framework\TestCase;

/**
 * @covers Config
 */
final class ConfigTest extends TestCase
{
    final public function testIsSitesEnabled()
    {
        $config = new Config(['enable_sites' => false]);
        $this->assertEquals(false, $config->isSitesEnabled());

        $config = new Config(['enable_sites' => true]);
        $this->assertEquals(true, $config->isSitesEnabled());
    }

    final public function testIsDefaultEnabled()
    {
        $config = new Config([]);
        $this->assertEquals(false, $config->isDefaultEnabled());

        $config = new Config(['default_site' => 'example']);
        $this->assertEquals(true, $config->isDefaultEnabled());
        $this->assertEquals('example', $config->getDefaultSiteCode());
    }

    final public function testIsDynamicEnabled()
    {
        $config = new Config(['enable_dynamic' => false]);
        $this->assertEquals(false, $config->isDynamicEnabled());

        $config = new Config(['enable_dynamic' => true]);
        $this->assertEquals(true, $config->isDynamicEnabled());
    }

    final public function testIsPresetsEnabled()
    {
        $config = new Config(['enable_presets' => false]);
        $this->assertEquals(false, $config->isPresetsEnabled());

        $config = new Config(['enable_presets' => true]);
        $this->assertEquals(true, $config->isPresetsEnabled());
    }

    final public function testGetUrls()
    {
        $config = new Config([
            'default_site' => 'default',
            'sites' => [
                'default' => [
                    'url' => 'http://example.com'
                ]
            ]
        ]);

        $this->assertEquals('http://example.com', $config->getDefaultUrl());
        $this->assertEquals('http://example.com', $config->getSiteUrl('default'));
    }

    final public function testGetUrlsExceptions()
    {
        $config = new Config([]);

        $this->expectException(NotFoundException::class);
        $config->getSiteUrl('default');
    }

    final public function testDefaultSiteConfigurationExceptions()
    {
        $config = new Config([]);

        $this->expectException(ConfigurationException::class);
        $config->getDefaultUrl();
    }

    final public function testGetProcessingConfig()
    {
        $config = new Config([
            'sites' => [
                'default' => [
                    'presets' => [
                        'example' => ['test_sites'],
                    ]
                ]
            ],
            'presets' => [
                'example' => ['test_global'],
            ]
        ]);

        $this->assertEquals(['test_sites'], $config->getProcessingConfig('example', 'default'));
        $this->assertEquals(['test_global'], $config->getProcessingConfig('example'));
    }

    final public function testLimits()
    {
        $config = new Config([
            'limits' => [
                'height' => 100,
                'width' => 200,
            ],
        ]);

        $this->assertEquals(100, $config->getHeightLimit());
        $this->assertEquals(200, $config->getWidthLimit());
    }
}