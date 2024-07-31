<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CreateCampaignHandler;
use App\InputFilter\CampaignInputFilter;
use App\Service\CampaignsService;
use Laminas\I18n\Translator\Translator;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreateCampaignHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CreateCampaignHandler
    {
        return new CreateCampaignHandler(
            $container->get(CampaignsService::class),
            $container->get(InputFilterPluginManager::class)->get(CampaignInputFilter::class),
            $container->get(Translator::class)
        );
    }
}