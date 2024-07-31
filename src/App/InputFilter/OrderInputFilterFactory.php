<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class OrderInputFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): OrderInputFilter
    {
        return new OrderInputFilter($container->get(InputFilterPluginManager::class)->get(ServiceInputFilter::class));
    }
}
