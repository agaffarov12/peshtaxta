<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ProductDetailsHandler;
use App\Service\ProductRepository;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProductDetailsHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ProductDetailsHandler {
        return new ProductDetailsHandler($container->get(ProductRepository::class));
    }
}
