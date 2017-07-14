<?php

namespace Owja\ImageProxyBundle\Command;

use Owja\ImageProxyBundle\Service\Proxy;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OwjaImageproxyCleanCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('owja:imageproxy:clean')
            ->setDescription('Clean up the image cache')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Proxy $proxy */
        $proxy = $this->getContainer()->get('owja_image_proxy.proxy');

        $proxy->cleanImageCache();

        $output->writeln('Image cache deleted.');
    }

}
