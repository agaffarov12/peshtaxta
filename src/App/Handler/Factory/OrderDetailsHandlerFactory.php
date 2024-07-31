<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\OrderDetailsHandler;
use App\Service\OrderRepository;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class OrderDetailsHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): OrderDetailsHandler
    {
        return new OrderDetailsHandler($container->get(OrderRepository::class));
    }
}
