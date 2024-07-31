<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CampaignDetailsHandler;
use App\Service\CampaignRepository;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CampaignDetailsHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): CampaignDetailsHandler
    {
        return new CampaignDetailsHandler($container->get(CampaignRepository::class));
    }
}
