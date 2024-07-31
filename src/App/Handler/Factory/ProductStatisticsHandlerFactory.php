<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ProductStatisticsHandler;
use App\Service\StatisticsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProductStatisticsHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ProductStatisticsHandler
    {
        return new ProductStatisticsHandler($container->get(StatisticsService::class));
    }
}