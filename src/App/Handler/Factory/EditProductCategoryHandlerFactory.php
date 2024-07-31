<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\EditProductCategoryHandler;
use App\InputFilter\CategoryInputFilter;
use App\Service\ProductCategoryService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EditProductCategoryHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): EditProductCategoryHandler
    {
        return new EditProductCategoryHandler(
            $container->get(ProductCategoryService::class),
            $container->get(InputFilterPluginManager::class)->get(CategoryInputFilter::class)
        );
    }
}
