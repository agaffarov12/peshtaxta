<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CreateTransactionHandler;
use App\InputFilter\TransactionInputFilter;
use App\Service\TransactionsService;
use Laminas\I18n\Translator\Translator;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreateTransactionHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): CreateTransactionHandler
    {
        return new CreateTransactionHandler(
            $container->get(TransactionsService::class),
            $container->get(InputFilterPluginManager::class)->get(TransactionInputFilter::class),
            $container->get(Translator::class)
        );
    }
}
