<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\DeleteCampaignHandler;
use App\Service\CampaignsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeleteCampaignHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): DeleteCampaignHandler
    {
        return new DeleteCampaignHandler($container->get(CampaignsService::class));
    }
}
