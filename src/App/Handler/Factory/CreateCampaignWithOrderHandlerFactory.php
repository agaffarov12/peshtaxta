<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CreateCampaignWithOrderHandler;
use App\InputFilter\OrderWithCampaignInputFilter;
use App\Service\OrdersService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreateCampaignWithOrderHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): CreateCampaignWithOrderHandler
    {
        return new CreateCampaignWithOrderHandler(
            $container->get(OrdersService::class),
            $container->get(InputFilterPluginManager::class)->get(OrderWithCampaignInputFilter::class)
        );
    }
}
