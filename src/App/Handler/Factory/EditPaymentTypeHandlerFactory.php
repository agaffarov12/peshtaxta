<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\EditPaymentTypeHandler;
use App\InputFilter\CategoryInputFilter;
use App\Service\PaymentTypesService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EditPaymentTypeHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): EditPaymentTypeHandler
    {
        return new EditPaymentTypeHandler(
            $container->get(PaymentTypesService::class),
            $container->get(InputFilterPluginManager::class)->get(CategoryInputFilter::class)
        );
    }
}
