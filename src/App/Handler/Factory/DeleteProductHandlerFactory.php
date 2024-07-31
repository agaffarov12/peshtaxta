<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\DeleteProductHandler;
use App\Service\ProductsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeleteProductHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new DeleteProductHandler($container->get(ProductsService::class));
    }
}
