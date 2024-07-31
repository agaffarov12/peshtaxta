<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\EditIndividualClientHandler;
use App\InputFilter\DirectAdvertiserInputFilter;
use App\Service\ClientsService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EditIndividualClientHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): EditIndividualClientHandler
    {
        return new EditIndividualClientHandler(
            $container->get(ClientsService::class),
            $container->get(InputFilterPluginManager::class)->get(DirectAdvertiserInputFilter::class)
        );
    }
}
