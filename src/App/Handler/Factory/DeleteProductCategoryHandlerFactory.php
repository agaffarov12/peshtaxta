<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\DeleteProductCategoryHandler;
use App\Service\ProductCategoryService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeleteProductCategoryHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): DeleteProductCategoryHandler
    {
        return new DeleteProductCategoryHandler($container->get(ProductCategoryService::class));
    }
}
