<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\CreateTransactionCategoryHandler;
use App\InputFilter\CategoryInputFilter;
use App\Service\TransactionCategoriesService;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreateTransactionCategoryHandlerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): CreateTransactionCategoryHandler
    {
        return new CreateTransactionCategoryHandler(
            $container->get(TransactionCategoriesService::class),
            $container->get(InputFilterPluginManager::class)->get(CategoryInputFilter::class)
        );
    }
}
