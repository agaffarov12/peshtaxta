<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ToggleRegionsHandler;
use App\Service\RegionsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ToggleRegionsHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ToggleRegionsHandler
    {
        return new ToggleRegionsHandler($container->get(RegionsService::class));
    }
}