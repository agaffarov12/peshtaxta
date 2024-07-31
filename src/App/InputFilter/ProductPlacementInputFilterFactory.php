<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ProductPlacementInputFilterFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ProductPlacementInputFilter
    {
        return new ProductPlacementInputFilter($container->get('config')['uploads_directories']['placements']);
    }
}
