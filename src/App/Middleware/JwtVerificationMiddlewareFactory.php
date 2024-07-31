<?php
declare(strict_types=1);

namespace App\Middleware;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class JwtVerificationMiddlewareFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): JwtVerificationMiddleware
    {
        $publicKey = file_get_contents("/var/www/data/public_key");

        return new JwtVerificationMiddleware($publicKey);
    }
}
