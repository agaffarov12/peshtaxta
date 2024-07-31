<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\AddBookingHandler;
use App\InputFilter\BookingInputFilter;
use App\Service\ProductsService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AddBookingHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AddBookingHandler
    {
        return new AddBookingHandler(
            $container->get(ProductsService::class),
            $container->get(InputFilterPluginManager::class)->get(BookingInputFilter::class)
        );
    }
}
