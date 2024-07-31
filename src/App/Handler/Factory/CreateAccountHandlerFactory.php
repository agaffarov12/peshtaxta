<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CreateAccountHandler;
use App\InputFilter\AccountInputFilter;
use App\Service\AccountsService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreateAccountHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): CreateAccountHandler
    {
        return new CreateAccountHandler(
            $container->get(AccountsService::class),
            $container->get(InputFilterPluginManager::class)->get(AccountInputFilter::class)
        );
    }
}
