<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\PaymentTypesListHandler;
use App\Service\PaymentTypesService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PaymentTypesListHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): PaymentTypesListHandler
    {
        return new PaymentTypesListHandler($container->get(PaymentTypesService::class));
    }
}
