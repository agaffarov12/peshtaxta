<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\ClientsService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ClientsListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly ClientsService $service)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $page        = $params['page'] ?? 1;
        $pageSize    = $params['pageSize'] ?? 10;
        $firstName   = $params['firstName'] ?? null;
        $lastName    = $params['lastName'] ?? null;
        $phoneNumber = $params['phoneNumber'] ?? null;
        $type        = $params['type'] ?? null;
        $category    = $params['category'] ?? null;
        $tags        = $params['tags'] ?? null;

        $paginator = $this->service->getListOfClients(
            $this->calculateOffset((int)$page, (int)$pageSize),
            (int)$pageSize,
            $firstName,
            $lastName,
            $category,
            $phoneNumber,
            $type,
            $tags,
        );

        return new JsonResponse(['data' => iterator_to_array($paginator), 'count' => $paginator->count()]);
    }

    private function calculateOffset(int $page, int $pageSize)
    {
        return ($page - 1) * $pageSize;
    }
}
