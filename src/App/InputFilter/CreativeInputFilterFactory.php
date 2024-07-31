<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreativeInputFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CreativeInputFilter
    {
        return new CreativeInputFilter($container->get('config')['uploads_directories']['creatives']);
    }
}
