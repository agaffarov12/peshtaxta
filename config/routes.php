<?php

declare(strict_types=1);

use App\Handler\AddClientCategoryHandler;
use App\Handler\AddOrderPaymentHandler;
use App\Handler\AddProductCategoryHandler;
use App\Handler\CampaignDetailsHandler;
use App\Handler\CampaignsListHandler;
use App\Handler\ClientOrdersListHandler;
use App\Handler\ClientDetailsHandler;
use App\Handler\CreateCampaignHandler;
use App\Handler\CreateCampaignWithOrderHandler;
use App\Handler\CreateOrderHandler;
use App\Handler\CreateProductHandler;
use App\Handler\DeleteCampaignHandler;
use App\Handler\DeleteClientCategoryHandler;
use App\Handler\DeleteClientHandler;
use App\Handler\DeleteProductCategoryHandler;
use App\Handler\DeleteProductHandler;
use App\Handler\EditAccountHandler;
use App\Handler\EditCampaignHandler;
use App\Handler\EditClientCategoryHandler;
use App\Handler\EditCompanyClientHandler;
use App\Handler\EditIndividualClientHandler;
use App\Handler\EditOrderHandler;
use App\Handler\EditPaymentTypeHandler;
use App\Handler\EditProductCategoryHandler;
use App\Handler\ExtendCampaignHandler;
use App\Handler\MountBannerHandler;
use App\Handler\OrderDetailsHandler;
use App\Handler\OrdersListHandler;
use App\Handler\ProductCategoriesListHandler;
use App\Handler\ProductsListHandler;
use App\Handler\RecreateCampaignHandler;
use App\Handler\TagsListHandler;
use App\Handler\ToggleRegionsHandler;
use Mezzio\Application;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

/**
 * FastRoute route configuration
 *
 * @see https://github.com/nikic/FastRoute
 *
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/{id:\d+}', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/', App\Handler\HomePageHandler::class, 'home');
    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');
    $app->get('/file/download/{id}', App\Handler\FileDownloadHandler::class, 'download_file');

    $app->get("/statistics", App\Handler\StatisticsHandler::class, 'statistics');
    $app->get("/statistics/clients", App\Handler\ClientStatisticsHandler::class, 'client_statistics');
    $app->get("/statistics/products", App\Handler\ProductStatisticsHandler::class, 'product_statistics');

    $app->get("/accounts", App\Handler\AccountsListHandler::class, 'list_accounts');
    $app->post("/accounts", [BodyParamsMiddleware::class, App\Handler\CreateAccountHandler::class, 'create_account']);
    $app->delete("/accounts/{id}", App\Handler\DeleteAccountHandler::class, 'delete_account');
    $app->patch("/accounts/{id}", [BodyParamsMiddleware::class, EditAccountHandler::class], 'edit_account');

    $app->get("/transactions", App\Handler\TransactionsListHandler::class, 'list_transactions');
    $app->post("/transactions", [BodyParamsMiddleware::class, App\Handler\CreateTransactionHandler::class], 'create_transaction');
    $app->post("/transactions/transfer", [BodyParamsMiddleware::class, App\Handler\TransferTransactionHandler::class, 'transfer_transaction']);

    $app->get("/transaction-categories", App\Handler\TransactionCategoriesListHandler::class, 'list_transaction_categories');
    $app->patch("/transaction-categories/{id}", [BodyParamsMiddleware::class, App\Handler\EditTransactionCategoryHandler::class], 'edit_transaction_category');
    $app->post("/transaction-categories", [BodyParamsMiddleware::class, App\Handler\CreateTransactionCategoryHandler::class], 'create_transaction_category');
    $app->delete("/transaction-categories/{id}", App\Handler\DeleteTransactionCategoryHandler::class, 'delete_transaction_category');

    $app->get('/insights', App\Handler\InsightsListHandler::class, 'list_insights');
    $app->post("/insights/{id}/mark-as-read", App\Handler\MarkInsightAsReadHandler::class, 'mark_insight_as_read');

    $app->get("/regions", App\Handler\RegionsListHandler::class, 'list_regions');
    $app->post("/regions", [BodyParamsMiddleware::class, ToggleRegionsHandler::class, 'toggle_regions']);

    $app->get('/payment-types', App\Handler\PaymentTypesListHandler::class, 'payment_types_list');
    $app->post('/payment-types', [BodyParamsMiddleware::class, App\Handler\CreatePaymentTypeHandler::class], 'create_payment_type');
    $app->patch('/payment-types/{id}', [BodyParamsMiddleware::class, EditPaymentTypeHandler::class], 'edit_payment_type');
    $app->delete('/payment-types/{id}', App\Handler\DeletePaymentTypeHandler::class, 'delete_payment_type');

    $app->get('/product-types', App\Handler\AccountsListHandler::class, 'product_types_list');
    $app->post('/product-types', [BodyParamsMiddleware::class, App\Handler\CreateAccountHandler::class], 'create_product_type');
    $app->delete('/product-types/{id}', App\Handler\DeleteAccountHandler::class, 'delete_product_type');

    $app->get("/client-origins", App\Handler\ClientOriginsListHandler::class, 'client_origin_list');
    $app->post("/client-origins", [BodyParamsMiddleware::class, App\Handler\CreateClientOriginHandler::class], "create_client_origin");
    $app->patch("/client-origins/{id}", [BodyParamsMiddleware::class, App\Handler\EditClientOriginHandler::class], 'edit_client_origin');
    $app->delete("/client-origins/{id}", App\Handler\DeleteClientOriginHandler::class, "delete_client_origin");

    $app->get("/clients", [App\Handler\ClientsListHandler::class], "list_clients");
    $app->get("/clients/categories", [App\Handler\ClientCategoriesListHandler::class], 'list_client_categories');
    $app->post("/clients/categories", [BodyParamsMiddleware::class, AddClientCategoryHandler::class], 'create_client_category');
    $app->delete("/clients/categories/{category}", [DeleteClientCategoryHandler::class], "delete_client_category");
    $app->patch("/clients/categories/{id}", [BodyParamsMiddleware::class, EditClientCategoryHandler::class], "edit_client_category");

    $app->get("/clients/{id}", [ClientDetailsHandler::class], "get_client_details");
    $app->delete("/clients/{id}", [DeleteClientHandler::class], 'delete_client');
    $app->get("/clients/individual/{id}", [App\Handler\IndividualClientDetailsHandler::class], 'individual_client_details');
    $app->get("/clients/company/{id}", [App\Handler\CompanyClientDetailsHandler::class], 'company_client_details');
    $app->post("/clients/individual/{id}", [EditIndividualClientHandler::class], 'edit_individual_client');
    $app->post("/clients/company/{id}", [EditCompanyClientHandler::class], 'edit_company_client');
    $app->post(
        "/clients/company",
        [BodyParamsMiddleware::class, App\Handler\CreateCompanyHandler::class],
        "create_company"
    );
    $app->post(
        "/clients/individual",
        [BodyParamsMiddleware::class, App\Handler\CreateDirectAdvertiserHandler::class],
        "create_individual"
    );

    $app->get("/product/{id}", App\Handler\ProductDetailsHandler::class, 'product_details');
    $app->post("/product/{id}", [App\Handler\UpdateProductHandler::class], 'update_product');
    $app->delete("/product/{id}", [DeleteProductHandler::class], 'delete_product');
    $app->get("/products", [ProductsListHandler::class], "list_products");
    $app->post("/products", [CreateProductHandler::class], "create_product");

    $app->get("/products/categories", [ProductCategoriesListHandler::class], "list_product_categories");
    $app->post("/products/categories", [BodyParamsMiddleware::class, AddProductCategoryHandler::class], "add_product_category");
    $app->delete("/products/categories/{category}", [DeleteProductCategoryHandler::class], "delete_product_category");
    $app->patch("/products/categories/{id}", [BodyParamsMiddleware::class, EditProductCategoryHandler::class], "edit_product_category");

    $app->post("/campaigns", [CreateCampaignHandler::class], "create_campaign");
    $app->get("/campaigns", [CampaignsListHandler::class], "show_campaigns");
    $app->get("/campaigns/{id}", [CampaignDetailsHandler::class], "show_campaign_details");
    $app->delete("/campaigns/{id}", DeleteCampaignHandler::class, "delete_campaigns");
    $app->post("/campaigns/{id}/edit", [EditCampaignHandler::class], "edit_campaign");
    $app->post("/campaigns/recreate", [BodyParamsMiddleware::class, RecreateCampaignHandler::class], "recreate_campaign");
    $app->post("/campaigns/{id}/toggle-banner", [MountBannerHandler::class], "toggle_banner");

    $app->post("/orders", [BodyParamsMiddleware::class, CreateOrderHandler::class], 'create_order');
    $app->post("/orders/{id}/edit", [BodyParamsMiddleware::class, EditOrderHandler::class], 'edit_order');
    $app->get("/orders/client/{id}", [ClientOrdersListHandler::class], 'show_orders_of_client');
    $app->post("/orders/{id}/extend-campaign", [BodyParamsMiddleware::class, ExtendCampaignHandler::class], 'extend_campaign');
    $app->post("/orders/create-with-campaign", [CreateCampaignWithOrderHandler::class], 'create_order_with_campaign');
    $app->get("/orders", [OrdersListHandler::class], "list_orders");
    $app->get("/orders/{id}", [OrderDetailsHandler::class], 'order_details');
    $app->post("/orders/{order}/add-payment", [BodyParamsMiddleware::class, AddOrderPaymentHandler::class], 'add_payment_to_order');

    $app->get("/tags", [TagsListHandler::class], 'list_tags' );
};
