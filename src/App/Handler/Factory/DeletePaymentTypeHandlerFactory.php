<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\DeletePaymentTypeHandler;
use App\Service\PaymentTypesService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeletePaymentTypeHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): DeletePaymentTypeHandler
    {
        return new DeletePaymentTypeHandler($container->get(PaymentTypesService::class));
    }
}
