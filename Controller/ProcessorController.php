<?php

namespace Owja\ImageProxyBundle\Controller;

use Owja\ImageProxyBundle\Service\Proxy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProcessorController extends Controller
{
    /**
     * @Route("/{site}/{type}/{width}x{height}/{image}",
     *     name          ="process_site",
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
     * @param string $site
     * @param string $type
     * @param int $width
     * @param int $height
     * @param string $image
     *
     * @return Response
     */
    public function siteProcessAction(string $site, string $type, $width, $height, string $image)
    {
        /** @var Proxy $proxy */
        $proxy = $this->container->get('owja_image_proxy.proxy');

        if ($this->container->getParameter('owja_image_proxy.enable_sites') !== true) {
            throw new NotFoundHttpException("Site processing is disabled.");
        }

        $url = "http://owja.de/";   // ToDo load Site Configuration

        $this->checkSize($width, $height);

        $proxy
            ->setHeight((int) $height)
            ->setWidth((int) $width)
            ->setProcessType($type)
            ->setNamespace($site)
            ->setUrl($url . $image);

        return new Response($proxy->getContent(), 200, [
            'Content-Type' => $proxy->getMimeType()
        ]);
    }

    /**
     * @Route("/{type}/{width}x{height}/{image}",
     *     name          ="process_default",
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
     * @param string $type
     * @param int $width
     * @param int $height
     * @param string $image
     *
     * @return Response
     */
    public function defaultProcessAction(string $type, $width, $height, string $image)
    {
        /** @var Proxy $proxy */
        $proxy = $this->container->get('owja_image_proxy.proxy');

        if (null === $url = $this->container->getParameter('owja_image_proxy.default_url')) {
            throw new NotFoundHttpException("owja_image_proxy.default_url is not configured.");
        }

        $this->checkSize($width, $height);

        $proxy
            ->setHeight((int) $height)
            ->setWidth((int) $width)
            ->setProcessType($type)
            ->setNamespace('default')
            ->setUrl($url . $image);

        return new Response($proxy->getContent(), 200, [
            'Content-Type' => $proxy->getMimeType()
        ]);
    }

    /**
     * Check the Size
     *
     * @param int $width
     * @param int $height
     */
    protected function checkSize(int $width, int $height)
    {
        if ((
                $this->container->getParameter('owja_image_proxy.limit_height')
                && $height > $this->container->getParameter('owja_image_proxy.limit_height')
            ) || (
                $this->container->getParameter('owja_image_proxy.limit_width')
                && $width > $this->container->getParameter('owja_image_proxy.limit_width')
            )) {
            throw new BadRequestHttpException('Imagesize not supported.');
        }

    }
}
