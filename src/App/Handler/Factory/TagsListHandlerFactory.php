<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\TagsListHandler;
use App\Service\TagsService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TagsListHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TagsListHandler
    {
        return new TagsListHandler($container->get(TagsService::class));
    }
}
