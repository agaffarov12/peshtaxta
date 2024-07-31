<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\UpdateProductHandler;
use App\InputFilter\ProductInputFilter;
use App\Service\ProductsService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UpdateProductHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): UpdateProductHandler {
        return new UpdateProductHandler(
            $container->get(ProductsService::class),
            $container->get(InputFilterPluginManager::class)->get(ProductInputFilter::class)
        );
    }
}
