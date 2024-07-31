<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\StatisticsHandler;
use App\Service\CampaignRepository;
use App\Service\ClientsService;
use App\Service\OrderRepository;
use App\Service\StatisticsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StatisticsHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): StatisticsHandler
    {
        return new StatisticsHandler(
            $container->get(StatisticsService::class),
        );
    }
}
