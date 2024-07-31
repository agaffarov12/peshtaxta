<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\RegionsListHandler;
use App\Service\RegionsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RegionsListHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): RegionsListHandler
    {
        return new RegionsListHandler($container->get(RegionsService::class));
    }
}