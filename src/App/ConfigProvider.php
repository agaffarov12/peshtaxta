<?php

declare(strict_types=1);

namespace App;

use App\InputFilter\AccountInputFilter;
use App\InputFilter\CampaignInputFilter;
use App\InputFilter\CategoryInputFilter;
use App\InputFilter\ClientInputFilter;
use App\InputFilter\CompanyInputFilter;
use App\InputFilter\CompanyInputFilterFactory;
use App\InputFilter\ContactDetailsInputFilter;
use App\InputFilter\CreativeInputFilter;
use App\InputFilter\CreativeInputFilterFactory;
use App\InputFilter\DirectAdvertiserInputFilter;
use App\InputFilter\DirectAdvertiserInputFilterFactory;
use App\InputFilter\ExtendCampaignInputFilter;
use App\InputFilter\LocationInputFilter;
use App\InputFilter\MoneyInputFilter;
use App\InputFilter\OrderInputFilter;
use App\InputFilter\OrderInputFilterFactory;
use App\InputFilter\OrderPaymentInputFilter;
use App\InputFilter\OrderWithCampaignInputFilter;
use App\InputFilter\ProductInputFilter;
use App\InputFilter\ProductInputFilterFactory;
use App\InputFilter\ProductPlacementInputFilter;
use App\InputFilter\ProductPlacementInputFilterFactory;
use App\InputFilter\ServiceInputFilter;
use App\InputFilter\TransactionInputFilter;
use App\InputFilter\TransferTransactionInputFilter;
use Laminas\I18n\Translator\Translator;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies'        => $this->getDependencies(),
            'templates'           => $this->getTemplates(),
            'input_filters'       => $this->getInputFilters(),
            'uploads_directories' => $this->getUploadsDirectories()
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'factories'  => [
                Handler\HomePageHandler::class                => Handler\HomePageHandlerFactory::class,
                Handler\ClientsListHandler::class             => Handler\Factory\ClientsListHandlerFactory::class,
                Handler\CreateDirectAdvertiserHandler::class  => Handler\Factory\CreateDirectAdvertiserHandlerFactory::class,
                Handler\CreateCompanyHandler::class           => Handler\Factory\CreateCompanyHandlerFactory::class,
                Handler\CreateProductHandler::class           => Handler\Factory\CreateProductHandlerFactory::class,
                Handler\ClientCategoriesListHandler::class    => Handler\Factory\ClientCategoriesListHandlerFactory::class,
                Handler\ProductsListHandler::class            => Handler\Factory\ProductsListHandlerFactory::class,
                Handler\ProductCategoriesListHandler::class   => Handler\Factory\ProductCategoriesListHandlerFactory::class,
                Handler\ProductDetailsHandler::class          => Handler\Factory\ProductDetailsHandlerFactory::class,
                Handler\IndividualClientDetailsHandler::class => Handler\Factory\IndividualClientDetailsFactory::class,
                Handler\TagsListHandler::class                => Handler\Factory\TagsListHandlerFactory::class,
                Handler\CompanyClientDetailsHandler::class    => Handler\Factory\CompanyClientDetailsHandlerFactory::class,
                Handler\CreateCampaignHandler::class          => Handler\Factory\CreateCampaignHandlerFactory::class,
                Handler\AddBookingHandler::class              => Handler\Factory\AddBookingHandlerFactory::class,
                Handler\CampaignsListHandler::class           => Handler\Factory\CampaignsListHandlerFactory::class,
                Handler\CampaignDetailsHandler::class         => Handler\Factory\CampaignDetailsHandlerFactory::class,
                Handler\CreateOrderHandler::class             => Handler\Factory\CreateOrderHandlerFactory::class,
                Handler\AddProductCategoryHandler::class      => Handler\Factory\AddProductCategoryHandlerFactory::class,
                Handler\DeleteProductCategoryHandler::class   => Handler\Factory\DeleteProductCategoryHandlerFactory::class,
                Handler\AddClientCategoryHandler::class       => Handler\Factory\AddClientCategoryHandlerFactory::class,
                Handler\DeleteClientCategoryHandler::class    => Handler\Factory\DeleteClientCategoryHandlerFactory::class,
                Handler\OrdersListHandler::class              => Handler\Factory\OrdersListHandlerFactory::class,
                Handler\OrderDetailsHandler::class            => Handler\Factory\OrderDetailsHandlerFactory::class,
                Handler\AddOrderPaymentHandler::class         => Handler\Factory\AddOrderPaymentHandlerFactory::class,
                Handler\UpdateProductHandler::class           => Handler\Factory\UpdateProductHandlerFactory::class,
                Handler\MountBannerHandler::class             => Handler\Factory\MountBannerHandlerFactory::class,
                Handler\FileDownloadHandler::class            => Handler\Factory\FileDownloadHandlerFactory::class,
                Handler\InsightsListHandler::class            => Handler\Factory\InsightsListHandlerFactory::class,
                Handler\MarkInsightAsReadHandler::class       => Handler\Factory\MarkInsightAsReadHandlerFactory::class,
                Handler\ExtendCampaignHandler::class          => Handler\Factory\ExtendCampaignHandlerFactory::class,
                Handler\ClientDetailsHandler::class           => Handler\Factory\ClientDetailsHandlerFactory::class,
                Handler\RecreateCampaignHandler::class        => Handler\Factory\RecreateCampaignHandlerFactory::class,
                Handler\EditIndividualClientHandler::class    => Handler\Factory\EditIndividualClientHandlerFactory::class,
                Handler\CreateCampaignWithOrderHandler::class => Handler\Factory\CreateCampaignWithOrderHandlerFactory::class,
                Handler\EditCompanyClientHandler::class       => Handler\Factory\EditCompanyClientHandlerFactory::class,  
                Handler\StatisticsHandler::class              => Handler\Factory\StatisticsHandlerFactory::class,
                Handler\DeleteProductHandler::class           => Handler\Factory\DeleteProductHandlerFactory::class,
                Handler\DeleteClientHandler::class            => Handler\Factory\DeleteClientHandlerFactory::class,
                Handler\ClientOriginsListHandler::class       => Handler\Factory\ClientOriginsListHandlerFactory::class,
                Handler\CreateClientOriginHandler::class      => Handler\Factory\CreateClientOriginHandlerFactory::class,
                Handler\DeleteClientOriginHandler::class      => Handler\Factory\DeleteClientOriginHandlerFactory::class,
                Handler\PaymentTypesListHandler::class        => Handler\Factory\PaymentTypesListHandlerFactory::class,
                Handler\CreatePaymentTypeHandler::class       => Handler\Factory\CreatePaymentTypeHandlerFactory::class,
                Handler\DeletePaymentTypeHandler::class       => Handler\Factory\DeletePaymentTypeHandlerFactory::class,
                Handler\ClientStatisticsHandler::class        => Handler\Factory\ClientStatisticsHandlerFactory::class,
                Handler\ProductStatisticsHandler::class       => Handler\Factory\ProductStatisticsHandlerFactory::class,
                Handler\AccountsListHandler::class            => Handler\Factory\AccountsListHandlerFactory::class,
                Handler\CreateAccountHandler::class             => Handler\Factory\CreateAccountHandlerFactory::class,
                Handler\DeleteAccountHandler::class             => Handler\Factory\DeleteAccountHandlerFactory::class,
                Handler\CreateTransactionHandler::class         => Handler\Factory\CreateTransactionHandlerFactory::class,
                Handler\TransactionsListHandler::class          => Handler\Factory\TransactionsListHandlerFactory::class,
                Handler\TransferTransactionHandler::class       => Handler\Factory\TransferTransactionHandlerFactory::class,
                Handler\EditAccountHandler::class               => Handler\Factory\EditAccountHandlerFactory::class,
                Handler\RegionsListHandler::class               => Handler\Factory\RegionsListHandlerFactory::class,
                Handler\ToggleRegionsHandler::class             => Handler\Factory\ToggleRegionsHandlerFactory::class,
                Handler\EditCampaignHandler::class              => Handler\Factory\EditCampaignHandlerFactory::class,
                Handler\DeleteCampaignHandler::class            => Handler\Factory\DeleteCampaignHandlerFactory::class,
                Handler\ClientOrdersListHandler::class          => Handler\Factory\ClientOrdersListHandlerFactory::class,
                Handler\EditOrderHandler::class                 => Handler\Factory\EditOrderHandlerFactory::class,
                Handler\CreateTransactionCategoryHandler::class =>
                    Handler\Factory\CreateTransactionCategoryHandlerFactory::class,
                Handler\TransactionCategoriesListHandler::class =>
                    Handler\Factory\TransactionCategoriesListHandlerFactory::class,
                Handler\DeleteTransactionCategoryHandler::class =>
                    Handler\Factory\DeleteTransactionCategoryHandlerFactory::class,
                Handler\EditTransactionCategoryHandler::class   =>
                    Handler\Factory\EditTransactionCategoryHandlerFactory::class,
                Handler\EditPaymentTypeHandler::class           =>
                    Handler\Factory\EditPaymentTypeHandlerFactory::class,
                Handler\EditClientOriginHandler::class =>
                    Handler\Factory\EditClientOriginHandlerFactory::class,
                Handler\EditProductCategoryHandler::class =>
                    Handler\Factory\EditProductCategoryHandlerFactory::class,
                Handler\EditClientCategoryHandler::class =>
                    Handler\Factory\EditClientCategoryHandlerFactory::class,

                Service\ClientsService::class         => Service\ClientsServiceFactory::class,
                Service\RegionsService::class         => Service\RegionsServiceFactory::class,
                Service\ClientCategoryService::class  => Service\ClientCategoryServiceFactory::class,
                Service\ProductCategoryService::class => Service\ProductCategoryServiceFactory::class,
                Service\TagsService::class         => Service\TagsServiceFactory::class,
                Service\ProductsService::class     => Service\ProductsServiceFactory::class,
                Service\ProductRepository::class   => Service\ProductRepositoryFactory::class,
                Service\CampaignRepository::class  => Service\CampaignRepositoryFactory::class,
                Service\OrderRepository::class     => Service\OrderRepositoryFactory::class,
                Service\OrdersService::class       => Service\OrdersServiceFactory::class,
                Service\FilesService::class        => Service\FilesServiceFactory::class,
                Service\InsightsService::class     => Service\InsightsServiceFactory::class,
                Service\ClientOriginService::class => Service\ClientOriginServiceFactory::class,
                Service\PaymentTypesService::class => Service\PaymentTypesServiceFactory::class,
                Service\StatisticsService::class   => Service\StatisticsServiceFactory::class,
                Service\PaymentsService::class     => Service\PaymentsServiceFactory::class,
                Service\CampaignsService::class    => Service\CampaignsServiceFactory::class,
                Service\AccountsService::class     => Service\AccountsServiceFactory::class,
                Service\TransactionsService::class => Service\TransactionsServiceFactory::class,
                Service\TransactionCategoriesService::class => Service\TransactionCategoriesServiceFactory::class,

                Middleware\LocaleMiddleware::class          => Middleware\LocaleMiddlewareFactory::class,
                Middleware\JwtVerificationMiddleware::class => Middleware\JwtVerificationMiddlewareFactory::class,

                Translator::class => Service\TranslatorFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'    => ['templates/app'],
                'error'  => ['templates/error'],
                'layout' => ['templates/layout'],
            ],
        ];
    }

    public function getInputFilters(): array
    {
        return [
            'invokables' => [
                ClientInputFilter::class              => ClientInputFilter::class,
                CategoryInputFilter::class            => CategoryInputFilter::class,
                ContactDetailsInputFilter::class      => ContactDetailsInputFilter::class,
                CampaignInputFilter::class            => CampaignInputFilter::class,
                LocationInputFilter::class            => LocationInputFilter::class,
                MoneyInputFilter::class               => MoneyInputFilter::class,
                OrderPaymentInputFilter::class        => OrderPaymentInputFilter::class,
                ExtendCampaignInputFilter::class      => ExtendCampaignInputFilter::class,
                OrderWithCampaignInputFilter::class   => OrderWithCampaignInputFilter::class,
                ServiceInputFilter::class             => ServiceInputFilter::class,
                AccountInputFilter::class             => AccountInputFilter::class,
                TransactionInputFilter::class         => TransactionInputFilter::class,
                TransferTransactionInputFilter::class => TransferTransactionInputFilter::class,
            ],
            'factories'  => [
                ProductPlacementInputFilter::class => ProductPlacementInputFilterFactory::class,
                CompanyInputFilter::class          => CompanyInputFilterFactory::class,
                DirectAdvertiserInputFilter::class => DirectAdvertiserInputFilterFactory::class,
                ProductInputFilter::class          => ProductInputFilterFactory::class,
                CreativeInputFilter::class         => CreativeInputFilterFactory::class,
                OrderInputFilter::class            => OrderInputFilterFactory::class,
            ],
        ];
    }

    public function getUploadsDirectories(): array
    {
        return [
            'clients'   => "./public/files/clients",
            'products'  => "./public/files/products",
            'placements' => "./public/files/placements",
            'creatives' => "./public/files/creatives",
        ];
    }
}
