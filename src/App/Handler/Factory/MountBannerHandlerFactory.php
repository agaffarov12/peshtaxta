<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\MountBannerHandler;
use App\Service\CampaignsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MountBannerHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): MountBannerHandler
    {
        return new MountBannerHandler($container->get(CampaignsService::class));
    }
}
