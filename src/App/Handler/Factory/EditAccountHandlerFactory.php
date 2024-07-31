<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\EditAccountHandler;
use App\InputFilter\AccountInputFilter;
use App\Service\AccountsService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EditAccountHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): EditAccountHandler {
        return new EditAccountHandler(
            $container->get(AccountsService::class),
            $container->get(InputFilterPluginManager::class)->get(AccountInputFilter::class)
        );
    }
}
