<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\AddClientCategoryHandler;
use App\InputFilter\CategoryInputFilter;
use App\Service\ClientCategoryService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AddClientCategoryHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): AddClientCategoryHandler
    {
        return new AddClientCategoryHandler(
            $container->get(ClientCategoryService::class),
            $container->get(InputFilterPluginManager::class)->get(CategoryInputFilter::class)
        );
    }
}
