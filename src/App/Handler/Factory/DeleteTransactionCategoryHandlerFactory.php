<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\DeleteTransactionCategoryHandler;
use App\Service\TransactionCategoriesService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeleteTransactionCategoryHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): DeleteTransactionCategoryHandler
    {
        return new DeleteTransactionCategoryHandler($container->get(TransactionCategoriesService::class));
    }
}
