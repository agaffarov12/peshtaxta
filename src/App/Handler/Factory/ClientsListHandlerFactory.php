<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ClientsListHandler;
use App\Service\ClientsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ClientsListHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ClientsListHandler
    {
        return new ClientsListHandler($container->get(ClientsService::class));
    }
}
