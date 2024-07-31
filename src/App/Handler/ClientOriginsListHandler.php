<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\ClientOriginService;
use App\Utils\Pagination;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClientOriginsListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly ClientOriginService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params    = $request->getQueryParams(); 

        $page      = $params['page'] ?? 1;
        $pageSize  = $params['pageSize'] ?? 100;

        $paginator = $this->service->list(Pagination::calculateOffset((int) $page, (int) $pageSize), (int) $pageSize);

        return new JsonResponse(
            ['data' => iterator_to_array($paginator), 'count' => $paginator->count()],
            StatusCodeInterface::STATUS_OK
        );
    }
}
