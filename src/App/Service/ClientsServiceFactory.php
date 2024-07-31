<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ClientsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ClientsService
    {
        return new ClientsService(
            $container->get("doctrine.entity_manager.orm_default"),
            $container->get(ClientCategoryService::class),
            $container->get(TagsService::class),
            $container->get(ClientOriginService::class)
        );
    }
}
