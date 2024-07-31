<?php
declare(strict_types=1);

namespace App\InputFilter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CompanyInputFilterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CompanyInputFilter
    {
        return new CompanyInputFilter($container->get('config')['uploads_directories']['clients']);
    }
}
