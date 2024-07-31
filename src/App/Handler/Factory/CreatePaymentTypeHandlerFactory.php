<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CreatePaymentTypeHandler;
use App\Service\PaymentTypesService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreatePaymentTypeHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): CreatePaymentTypeHandler {
        return new CreatePaymentTypeHandler($container->get(PaymentTypesService::class));
    }
}
