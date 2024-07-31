<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\InsightsListHandler;
use App\Service\InsightsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class InsightsListHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): InsightsListHandler
    {
        return new InsightsListHandler($container->get(InsightsService::class));
    }
}
