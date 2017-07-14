<?php

namespace Owja\ImageProxyBundle\Controller;

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
    public function siteProcessAction(Request $request, string $site, string $type, $width, $height, string $image)
    {
        /** @var Proxy $proxy */
        $proxy = $this->container->get('owja_image_proxy.proxy');

        /** @var Config $config */
        $config = $this->container->get('owja_image_proxy.config');

        if (false === $config->isSitesEnabled()) {
            throw new NotFoundHttpException("Site processing is disabled.");
        }

        $url = $config->getSiteUrl($site);

        $this->checkSize((int) $width, (int) $height);

        $proxy
            ->setHeight((int) $height)
            ->setWidth((int) $width)
            ->setProcessType($type)
            ->setNamespace($site)
            ->setUrl($url . $image);

        return $this->createResponse($proxy, $request);
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
     * @param Request $request
     *
     * @param string $type
     * @param int $width
     * @param int $height
     * @param string $image
     *
     * @return Response
     */
    public function defaultProcessAction(Request $request, string $type, $width, $height, string $image)
    {
        /** @var Proxy $proxy */
        $proxy = $this->container->get('owja_image_proxy.proxy');

        /** @var Config $config */
        $config = $this->container->get('owja_image_proxy.config');

        $url = $config->getDefaultUrl();
        $this->checkSize((int) $width, (int) $height);

        $proxy
            ->setHeight((int) $height)
            ->setWidth((int) $width)
            ->setProcessType($type)
            ->setNamespace('default')
            ->setUrl($url . $image);

        return $this->createResponse($proxy, $request);
    }

    /**
     * Create Response
     *
     * @param Proxy $proxy
     * @param Request $request
     * @return Response
     */
    protected function createResponse(Proxy $proxy, Request $request)
    {
        $response = new Response($proxy->getContent(), 200, [
            'Content-Type' => $proxy->getMimeType()
        ]);

        $response->setPublic();
        // $response->setLastModified((new \DateTime())->setTimestamp($proxy->getTimestamp()));
        $response->setExpires(new \DateTime('now +7 days'));
        $response->setEtag($proxy->getCacheTag());

        $response->isNotModified($request);

        return $response;
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
        /** @var Config $config */
        $config = $this->container->get('owja_image_proxy.config');

        if (($config->getHeightLimit() && $height > $config->getHeightLimit())
            || ($config->getWidthLimit() && $width > $config->getWidthLimit()))
        {
            throw new BadRequestHttpException('Image size not supported.');
        }

    }
}
