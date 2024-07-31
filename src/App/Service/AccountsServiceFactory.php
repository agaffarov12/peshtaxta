<?php
declare(strict_types=1);

namespace App\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AccountsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AccountsService
    {
        return new AccountsService(
            $container->get("doctrine.entity_manager.orm_default"),
            $container->get(PaymentTypesService::class)
        );
    }
}
