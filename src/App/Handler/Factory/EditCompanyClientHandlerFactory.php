<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\EditCompanyClientHandler;
use App\InputFilter\CompanyInputFilter;
use App\Service\ClientsService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EditCompanyClientHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): EditCompanyClientHandler {
        return new EditCompanyClientHandler(
            $container->get(ClientsService::class),
            $container->get(InputFilterPluginManager::class)->get(CompanyInputFilter::class)
        );
    }
}