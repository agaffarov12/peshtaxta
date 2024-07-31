<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\TransferTransactionHandler;
use App\InputFilter\TransferTransactionInputFilter;
use App\Service\TransactionsService;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Psr\Container\ContainerInterface;

class TransferTransactionHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ) {
        return new TransferTransactionHandler(
            $container->get(TransactionsService::class),
            $container->get(InputFilterPluginManager::class)->get(TransferTransactionInputFilter::class),
            $container->get(Translator::class)
        );
    }
}
