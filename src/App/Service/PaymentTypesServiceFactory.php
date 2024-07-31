<?php
declare(strict_types=1);

namespace App\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PaymentTypesServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): PaymentTypesService
    {
        return new PaymentTypesService($container->get("doctrine.entity_manager.orm_default"));
    }
}
