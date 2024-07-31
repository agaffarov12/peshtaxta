<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\DeleteClientCategoryHandler;
use App\Service\ClientCategoryService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeleteClientCategoryHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): DeleteClientCategoryHandler
    {
        return new DeleteClientCategoryHandler($container->get(ClientCategoryService::class));
    }
}
