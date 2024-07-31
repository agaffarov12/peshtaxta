<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\AddProductCategoryHandler;
use App\InputFilter\CategoryInputFilter;
use App\Service\ProductCategoryService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AddProductCategoryHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): AddProductCategoryHandler
    {
        return new AddProductCategoryHandler(
            $container->get(ProductCategoryService::class),
            $container->get(InputFilterPluginManager::class)->get(CategoryInputFilter::class)
        );
    }
}
