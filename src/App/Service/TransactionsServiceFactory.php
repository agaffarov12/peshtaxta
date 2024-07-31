<?php
declare(strict_types=1);

namespace App\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TransactionsServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TransactionsService
    {
        return new TransactionsService(
            $container->get("doctrine.entity_manager.orm_default"),
            $container->get(AccountsService::class),
            $container->get(TransactionCategoriesService::class)
        );
    }
}
