<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\DeleteClientOriginHandler;
use App\Service\ClientOriginService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeleteClientOriginHandlerFactory implements FactoryInterface
{

    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): DeleteClientOriginHandler
    {
        return new DeleteClientOriginHandler($container->get(ClientOriginService::class));
    }
}
