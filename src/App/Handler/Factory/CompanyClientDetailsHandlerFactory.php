<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CompanyClientDetailsHandler;
use App\Service\ClientsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CompanyClientDetailsHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): CompanyClientDetailsHandler {
        return new CompanyClientDetailsHandler($container->get(ClientsService::class));
    }
}
