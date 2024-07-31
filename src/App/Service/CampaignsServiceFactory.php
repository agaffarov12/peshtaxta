<?php
declare(strict_types=1);

namespace App\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CampaignsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CampaignsService
    {
        return new CampaignsService(
            $container->get(CampaignRepository::class),
            $container->get("doctrine.entity_manager.orm_default"),
            $container->get(InsightsService::class),
            $container->get(ProductsService::class),
        );
    }
}
