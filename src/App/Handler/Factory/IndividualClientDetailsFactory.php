<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\IndividualClientDetailsHandler;
use App\Service\ClientsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IndividualClientDetailsFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): IndividualClientDetailsHandler {
        return new IndividualClientDetailsHandler($container->get(ClientsService::class));
    }
}
