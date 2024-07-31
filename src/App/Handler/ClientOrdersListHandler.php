<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\OrderRepository;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class ClientOrdersListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly OrderRepository $repository)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $page      = $params['page'] ?? 1;
        $pageSize  = $params['pageSize'] ?? 10;

        $clientId = $request->getAttribute("id");

        if (!$clientId || !Uuid::isValid($clientId)) {
            return new JsonResponse([], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        return new JsonResponse(
            $this->repository->getOrdersOfClient(
                Uuid::fromString($clientId),
                $this->calculateOffset((int)$page, (int)$pageSize),
                (int) $pageSize
            )
        );
    }

    private function calculateOffset(int $page, int $pageSize)
    {
        return ($page - 1) * $pageSize;
    }
}
