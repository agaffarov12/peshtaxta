<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\FileDownloadHandler;
use App\Service\FilesService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class FileDownloadHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FileDownloadHandler
    {
        return new FileDownloadHandler($container->get(FilesService::class));
    }
}
