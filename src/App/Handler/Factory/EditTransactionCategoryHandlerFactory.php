<?php
declare(strict_types=1);

namespace App\Handler\Factory;

use App\Handler\EditTransactionCategoryHandler;
use App\Service\TransactionCategoriesService;
use App\InputFilter\CategoryInputFilter;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EditTransactionCategoryHandlerFactory implements FactoryInterface 
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): EditTransactionCategoryHandler
    {
        return new EditTransactionCategoryHandler(
            $container->get(TransactionCategoriesService::class),
            $container->get(InputFilterPluginManager::class)->get(CategoryInputFilter::class)
        );
    }
}