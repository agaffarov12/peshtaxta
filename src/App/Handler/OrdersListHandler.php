<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\OrderRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OrdersListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly OrderRepository $repository)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $page            = $params['page'] ?? 1;
        $pageSize        = $params['pageSize'] ?? 10;
        $clientFistName  = $params['clientFirstName'] ?? null;
        $clientLastName  = $params['clientLastName'] ?? null;
        $minPrice        = $params['minPrice'] ?? null;
        $maxPrice        = $params['maxPrice'] ?? null;
        $fullyPaid       = $params['fullyPaid'] ?? null;

        $result = $this->repository->list(
            $this->calculateOffset((int)$page, (int)$pageSize),
            (int)$pageSize,
            $clientFistName,
            $clientLastName,
            $minPrice ? (int) $minPrice : null,
            $maxPrice ? (int) $maxPrice : null,
            (bool) $fullyPaid
        );

        return new JsonResponse($result);
    }

    private function calculateOffset(int $page, int $pageSize)
    {
        return ($page - 1) * $pageSize;
    }
}
