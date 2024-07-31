<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CreateOrderHandler;
use App\InputFilter\OrderInputFilter;
use App\Service\OrdersService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreateOrderHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CreateOrderHandler
    {
        return new CreateOrderHandler(
            $container->get(OrdersService::class),
            $container->get(InputFilterPluginManager::class)->get(OrderInputFilter::class)
        );
    }
}
