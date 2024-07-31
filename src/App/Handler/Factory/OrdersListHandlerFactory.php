<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\OrdersListHandler;
use App\Service\OrderRepository;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class OrdersListHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): OrdersListHandler
    {
        return new OrdersListHandler($container->get(OrderRepository::class));
    }
}
