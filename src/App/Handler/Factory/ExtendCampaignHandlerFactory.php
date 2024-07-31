<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\ExtendCampaignHandler;
use App\InputFilter\ExtendCampaignInputFilter;
use App\Service\OrdersService;
use Laminas\I18n\Translator\Translator;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ExtendCampaignHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): ExtendCampaignHandler
    {
        return new ExtendCampaignHandler(
            $container->get(OrdersService::class),
            $container->get(InputFilterPluginManager::class)->get(ExtendCampaignInputFilter::class),
            $container->get(Translator::class)
        );
    }
}
