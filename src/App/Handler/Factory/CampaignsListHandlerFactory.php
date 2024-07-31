<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CampaignsListHandler;
use App\Service\CampaignRepository;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CampaignsListHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CampaignsListHandler
    {
        return new CampaignsListHandler($container->get(CampaignRepository::class));
    }
}
