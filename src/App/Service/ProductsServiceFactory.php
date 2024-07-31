<?php
declare(strict_types=1);

namespace App\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProductsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ProductsService
    {
        return new ProductsService(
            $container->get(ProductRepository::class),
            $container->get(ProductCategoryService::class),
            $container->get(TagsService::class),
            $container->get(StatisticsService::class)
        );
    }
}
