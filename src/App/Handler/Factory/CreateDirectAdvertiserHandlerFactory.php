<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CreateDirectAdvertiserHandler;
use App\InputFilter\DirectAdvertiserInputFilter;
use App\Service\ClientsService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreateDirectAdvertiserHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CreateDirectAdvertiserHandler
    {
        return new CreateDirectAdvertiserHandler(
            $container->get(InputFilterPluginManager::class)->get(DirectAdvertiserInputFilter::class), 
            $container->get(ClientsService::class)
        );
    }
}
