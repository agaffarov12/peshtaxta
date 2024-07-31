<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ClientStatisticsHandler;
use App\Service\StatisticsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ClientStatisticsHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ClientStatisticsHandler
    {
        return new ClientStatisticsHandler($container->get(StatisticsService::class));
    }
}
