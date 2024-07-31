<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ProductCategoriesListHandler;
use App\Service\ProductCategoryService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProductCategoriesListHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ProductCategoriesListHandler
    {
        return new ProductCategoriesListHandler($container->get(ProductCategoryService::class));
    }
}
