<?php
declare(strict_types=1);

namespace App\Middleware;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\Translator;

class LocaleMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LocaleMiddleware
    {
        return new LocaleMiddleware($container->get(Translator::class));
    }
}