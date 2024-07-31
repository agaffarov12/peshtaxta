<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DirectAdvertiserInputFilterFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): DirectAdvertiserInputFilter
    {
        return new DirectAdvertiserInputFilter($container->get('config')['uploads_directories']['clients']);
    }
}
