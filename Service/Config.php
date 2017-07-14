<?php
namespace Owja\ImageProxyBundle\Service;

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
        $this->config = $config;
    }

    /**
     * Is Sites enabled?
     *
     * @return bool
     */
    public function isSitesEnabled() : bool
    {
        return (bool) $this->config['enable_sites'];
    }

    /**
     * Is default site enabled?
     *
     * @return bool
     */
    public function isDefaultEnabled() : bool
    {
        return is_string($this->config['default_site']) && !empty($this->config['default_site']);
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

        return $this->getSiteUrl($this->config['default_site']);
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
        if (isset($this->config['sites'][$code])) {
            return $this->config['sites'][$code]['url'];
        }

        // ToDo trigger event and try to resolve the listener

        throw new NotFoundException("Site {$code} not found.");
    }

    /**
     * Get height limit
     *
     * @return int
     */
    public function getHeightLimit() : int
    {
        return (int) $this->config['limits']['height'];
    }

    /**
     * Get width limit
     *
     * @return int
     */
    public function getWidthLimit() : int
    {
        return (int) $this->config['limits']['width'];
    }
}