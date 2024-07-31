<?php
declare(strict_types=1);

namespace App\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TransactionCategoriesServiceFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): TransactionCategoriesService
    {
        return new TransactionCategoriesService($container->get("doctrine.entity_manager.orm_default"));
    }
}
