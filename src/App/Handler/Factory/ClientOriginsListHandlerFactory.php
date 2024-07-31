<?php

declare(strict_types=1);

namespace App\Handler\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use App\Handler\ClientOriginsListHandler;
use App\Service\ClientOriginService;
use Psr\Container\ContainerInterface;

class ClientOriginsListHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ClientOriginsListHandler
    {
        return new ClientOriginsListHandler($container->get(ClientOriginService::class));
    }
}
