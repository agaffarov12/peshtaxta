<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use App\Handler\CreateProductHandler;
use App\Service\ProductsService;
use App\InputFilter\ProductInputFilter;
use Laminas\InputFilter\InputFilterPluginManager;
use Psr\Container\ContainerInterface;

class CreateProductHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CreateProductHandler
    {
        return new CreateProductHandler(
            $container->get(ProductsService::class),
            $container->get(InputFilterPluginManager::class)->get(ProductInputFilter::class)
        );
    }
}
