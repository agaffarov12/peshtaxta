<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use App\Handler\ProductsListHandler;
use App\Service\ProductRepository;

class ProductsListHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ProductsListHandler
    {
        return new ProductsListHandler($container->get(ProductRepository::class));
    }
}
