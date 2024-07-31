<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\AddOrderPaymentHandler;
use App\InputFilter\OrderPaymentInputFilter;
use App\Service\OrdersService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AddOrderPaymentHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): AddOrderPaymentHandler
    {
        return new AddOrderPaymentHandler(
            $container->get(OrdersService::class),
            $container->get(InputFilterPluginManager::class)->get(OrderPaymentInputFilter::class)
        );
    }
}
