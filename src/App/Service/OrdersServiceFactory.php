<?php
declare(strict_types=1);

namespace App\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class OrdersServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): OrdersService
    {
        return new OrdersService(
            $container->get(OrderRepository::class),
            $container->get(CampaignRepository::class),
            $container->get(CampaignsService::class),
            $container->get(TagsService::class),
            $container->get(PaymentTypesService::class),
            $container->get(TransactionsService::class),
            $container->get(ClientsService::class)
        );
    }
}
