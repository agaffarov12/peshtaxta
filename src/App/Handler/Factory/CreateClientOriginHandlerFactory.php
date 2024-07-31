<?php

declare(strict_types=1);

namespace App\Handler\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use App\Handler\CreateClientOriginHandler;
use App\Service\ClientOriginService;

class CreateClientOriginHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): CreateClientOriginHandler {
        return new CreateClientOriginHandler($container->get(ClientOriginService::class));
    }
}