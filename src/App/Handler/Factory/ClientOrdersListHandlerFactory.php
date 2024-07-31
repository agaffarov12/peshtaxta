<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ClientOrdersListHandler;
use App\Service\OrderRepository;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ClientOrdersListHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ClientOrdersListHandler
    {
        return new ClientOrdersListHandler($container->get(OrderRepository::class));
    }
}
