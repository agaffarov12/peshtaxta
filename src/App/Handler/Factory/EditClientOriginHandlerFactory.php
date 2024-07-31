<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\EditClientOriginHandler;
use App\InputFilter\CategoryInputFilter;
use App\Service\ClientOriginService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EditClientOriginHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): EditClientOriginHandler
    {
        return new EditClientOriginHandler(
            $container->get(ClientOriginService::class),
            $container->get(InputFilterPluginManager::class)->get(CategoryInputFilter::class)
        );
    }
}
