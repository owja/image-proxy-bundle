<?php
namespace Owja\ImageProxyBundle\Service;

use Owja\Helper\Data;
use Owja\ImageProxyBundle\Exception\ConfigurationException;
use Owja\ImageProxyBundle\Exception\NotFoundException;

class Config
{
    /**
     * @var array
     */
    private $config;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = new Data($config);
    }

    /**
     * Is Sites enabled?
     *
     * @return bool
     */
    public function isSitesEnabled() : bool
    {
        return (bool) $this->get('enable_sites');
    }

    /**
     * Is default site enabled?
     *
     * @return bool
     */
    public function isDefaultEnabled() : bool
    {
        $site = $this->get('default_site');
        return is_string($site) && !empty($site);
    }

    /**
     * Get Default Site
     *
     * @return string
     */
    public function getDefaultSiteCode() : string
    {
        return $this->get('default_site');
    }

    /**
     * Is dynamic processing enabled?
     *
     * @return bool
     */
    public function isDynamicEnabled() : bool
    {
        return (bool) $this->get('enable_dynamic');
    }

    /**
     * Is processing by presets enabled?
     *
     * @return bool
     */
    public function isPresetsEnabled() : bool
    {
        return (bool) $this->get('enable_presets');
    }

    /**
     * Get the default site URL
     *
     * @return string
     * @throws ConfigurationException
     */
    public function getDefaultUrl() : string
    {
        if (!$this->isDefaultEnabled()) {
            throw new ConfigurationException('Default Site is not enabled');
        }

        return $this->getSiteUrl($this->get('default_site'));
    }

    /**
     * Get URL for site by code
     *
     * @param string $code
     * @return string
     * @throws NotFoundException
     */
    public function getSiteUrl(string $code) : string
    {
        if (null !== $url = $this->get("sites.{$code}.url")) {
            return $url;
        }

        // ToDo trigger event and try to resolve by the listeners

        throw new NotFoundException("Site \"{$code}\" not found.");
    }

    /**
     * Get processing configuration for site by code
     *
     * @param string $code
     * @param string $site
     * @return array
     * @throws NotFoundException
     */
    public function getProcessingConfig(string $code, string $site = null) : array
    {
        // Search for preset by site
        if ($site && null !== $config = $this->get("sites.{$site}.presets.{$code}")) {
            return $config;
        }

        // Search for global defined preset
        if (null !== $config = $this->get("presets.{$code}")) {
            return $config;
        }

        // ToDo trigger event and try to resolve by the listeners

        throw new NotFoundException("Preset \"{$code}\" not found.");
    }

    /**
     * Get height limit
     *
     * @return int
     */
    public function getHeightLimit() : int
    {
        return (int) $this->get('limits.height');
    }

    /**
     * Get width limit
     *
     * @return int
     */
    public function getWidthLimit() : int
    {
        return (int) $this->get('limits.width');
    }

    /**
     * Get config var
     *
     * @param string $var
     * @return mixed
     */
    protected function get(string $var)
    {
        return $this->config->get($var);
    }
}