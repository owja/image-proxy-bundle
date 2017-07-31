<?php
namespace Owja\ImageProxyBundle\Controller;

use Owja\ImageProxyBundle\Exception\NotFoundException;
use Owja\ImageProxyBundle\Service\Config;
use Owja\ImageProxyBundle\Service\Proxy;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProcessorController extends Controller
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Processing by site and preset configuration
     *
     * @Route("/{site}:{preset}/{image}",
     *     name          ="process_site_by_preset",
     *     requirements  = {
     *         "site":    "[a-z]+",
     *         "preset":  "[a-z]+",
     *         "image":   ".+"
     *     },
     *     methods       = {
     *         "GET"
     *     }
     * )
     *
     * @param Request $request
     *
     * @param string $site
     * @param string $preset
     * @param string $image
     *
     * @return Response
     */
    public function presetSiteProcessAction(Request $request, string $site, string $preset, string $image)
    {
        $this->restrict([ 'sites', 'presets' ]);

        try {
            $preset = $this->config()->getProcessingConfig($preset, $site);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->getResponse($request, $site, $preset['type'], $preset['width'], $preset['height'], $image);
    }

    /**
     * Processing by site and preset configuration
     *
     * @Route("/:{preset}/{image}",
     *     name          ="process_default_by_preset",
     *     requirements  = {
     *         "preset":  "[a-z]+",
     *         "image":   ".+"
     *     },
     *     methods       = {
     *         "GET"
     *     }
     * )
     *
     * @param Request $request
     *
     * @param string $preset
     * @param string $image
     *
     * @return Response
     */
    public function presetDefaultProcessAction(Request $request, string $preset, string $image)
    {
        $this->restrict([ 'default', 'presets' ]);

        try {
            $preset = $this->config()->getProcessingConfig($preset, $this->config()->getDefaultSiteCode());
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $this->getResponse($request, null, $preset['type'], $preset['width'], $preset['height'], $image);
    }

    /**
     * Dynamic processing by site configuration
     *
     * @Route("/{site}/{type}/{width}x{height}/{image}",
     *     name          ="dynamic_process_site",
     *     requirements  = {
     *         "site":    "[a-z]+",
     *         "type":    "resize|crop",
     *         "width":   "\d*",
     *         "height":  "\d*",
     *         "image":   ".+"
     *     },
     *     methods       = {
     *         "GET"
     *     }
     * )
     *
     * @param Request $request
     *
     * @param string $site
     * @param string $type
     * @param int $width
     * @param int $height
     * @param string $image
     *
     * @return Response
     */
    public function dynamicSiteProcessAction(Request $request, string $site, string $type, $width, $height, string $image)
    {
        $this->restrict([ 'dynamic', 'sites' ]);
        $this->checkSize((int) $width, (int) $height);

        return $this->getResponse($request, $site, $type, $width, $height, $image);
    }

    /**
     * Dynamic processing by default configuration
     *
     * @Route("/{type}/{width}x{height}/{image}",
     *     name          ="dynamic_process_default",
     *     requirements  = {
     *         "type":    "resize|crop",
     *         "width":   "\d*",
     *         "height":  "\d*",
     *         "image":   ".+"
     *     },
     *     methods       = {
     *         "GET"
     *     }
     * )
     *
     * @param Request $request
     *
     * @param string $type
     * @param int $width
     * @param int $height
     * @param string $image
     *
     * @return Response
     */
    public function dynamicDefaultProcessAction(Request $request, string $type, $width, $height, string $image)
    {
        $this->restrict([ 'dynamic', 'default' ]);
        $this->checkSize((int) $width, (int) $height);

        return $this->getResponse($request, null, $type, $width, $height, $image);
    }

    /**
     * Restrict to access types
     *
     * @param $types
     */
    protected function restrict(array $types)
    {
        if (in_array('default', $types) && false === $this->config()->isDefaultEnabled()) {
            throw new NotFoundHttpException("Default site is disabled.");
        }

        if (in_array('dynamic', $types) && false === $this->config()->isDynamicEnabled()) {
            throw new NotFoundHttpException("Dynamic processing is disabled.");
        }

        if (in_array('sites', $types) && false === $this->config()->isSitesEnabled()) {
            throw new NotFoundHttpException("Site processing is disabled.");
        }

        if (in_array('presets', $types) && false === $this->config()->isPresetsEnabled()) {
            throw new NotFoundHttpException("Processing by presets is disabled.");
        }
    }

    /**
     * Load Image and return Response
     *
     * @param Request $request
     * @param string $site
     * @param string $type
     * @param $width
     * @param $height
     * @param string $image
     * @return Response
     */
    protected function getResponse(Request $request, string $site = null, string $type, $width, $height, string $image)
    {
        /** @var Proxy $proxy */
        $proxy = $this->container->get('owja_image_proxy.proxy');

        $url       = ($site === null) ? $this->config()->getDefaultUrl()      : $this->config()->getSiteUrl($site);
        $namespace = ($site === null) ? $this->config()->getDefaultSiteCode() : $site;

        $proxy
            ->setHeight((int) $height)
            ->setWidth((int) $width)
            ->setProcessType($type)
            ->setNamespace($namespace)
            ->setUrl($url . $image);

        return $proxy->createResponse($request);
    }

    /**
     * Check the Size
     *
     * @param int $width
     * @param int $height
     * @throws BadRequestHttpException
     */
    protected function checkSize(int $width, int $height)
    {
        if (($this->config()->getHeightLimit() && $height > $this->config()->getHeightLimit())
            || ($this->config()->getWidthLimit() && $width > $this->config()->getWidthLimit()))
        {
            throw new BadRequestHttpException('Image size not supported.');
        }
    }

    /**
     * Image Proxy Configuration
     *
     * @return Config
     */
    protected function config()
    {
        if (!$this->config) {
            $this->config = $this->container->get('owja_image_proxy.config');
        }

        return $this->config;
    }
}
