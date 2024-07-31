<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\TransactionsListHandler;
use App\Service\TransactionsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TransactionsListHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): TransactionsListHandler
    {
        return new TransactionsListHandler($container->get(TransactionsService::class));
    }
}
