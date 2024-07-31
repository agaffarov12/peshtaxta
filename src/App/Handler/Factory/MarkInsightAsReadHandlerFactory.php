<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\MarkInsightAsReadHandler;
use App\Service\InsightsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MarkInsightAsReadHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): MarkInsightAsReadHandler {
        return new MarkInsightAsReadHandler($container->get(InsightsService::class));
    }
}
