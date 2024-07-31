<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ClientDetailsHandler;
use App\Service\ClientsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ClientDetailsHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ClientDetailsHandler
    {
        return new ClientDetailsHandler($container->get(ClientsService::class));
    }
}
