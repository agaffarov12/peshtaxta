<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\AccountsListHandler;
use App\Service\AccountsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AccountsListHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): AccountsListHandler
    {
        return new AccountsListHandler($container->get(AccountsService::class));
    }
}
