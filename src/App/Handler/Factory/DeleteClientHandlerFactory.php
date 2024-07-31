<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\DeleteClientHandler;
use App\Service\ClientsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeleteClientHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): DeleteClientHandler
    {
        return new DeleteClientHandler($container->get(ClientsService::class));
    }
}
