<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\TransactionsService;
use App\Utils\Pagination;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TransactionsListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly TransactionsService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 50;
        $category = $params['category'] ?? null;
        $account = $params['account'] ?? null;

        $paginator = $this->service->list(
            Pagination::calculateOffset((int) $page, (int) $pageSize),
            (int) $pageSize,
            $category,
            $account
        );

        return new JsonResponse(['data' => iterator_to_array($paginator), 'count' => $paginator->count()]);
    }
}
