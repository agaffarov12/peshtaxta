<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProductInputFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ProductInputFilter
    {
        return new ProductInputFilter(
            $container->get(InputFilterPluginManager::class)->get(ProductPlacementInputFilter::class),
            $container->get('config')['uploads_directories']['products']
        );
    }
}
