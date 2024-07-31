<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\TransactionCategoriesListHandler;
use App\Service\TransactionCategoriesService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TransactionCategoriesListHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): TransactionCategoriesListHandler
    {
        return new TransactionCategoriesListHandler($container->get(TransactionCategoriesService::class));
    }
}
