<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\DeleteAccountHandler;
use App\Service\AccountsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DeleteAccountHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): DeleteAccountHandler
    {
        return new DeleteAccountHandler($container->get(AccountsService::class));
    }
}
