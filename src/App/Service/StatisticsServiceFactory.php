<?php
declare(strict_types=1);

namespace App\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StatisticsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): StatisticsService
    {
        return new StatisticsService(
            $container->get(ClientsService::class),
            $container->get(CampaignRepository::class),
            $container->get(PaymentsService::class),
            $container->get(ProductRepository::class),
            $container->get(OrderRepository::class)
        );
    }
}
