<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ClientCategoriesListHandler;
use App\Service\ClientCategoryService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ClientCategoriesListHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ClientCategoriesListHandler
    {
        return new ClientCategoriesListHandler($container->get(ClientCategoryService::class));
    }
}
