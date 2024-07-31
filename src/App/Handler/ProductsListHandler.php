<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\ProductRepository;
use Laminas\Diactoros\Response\JsonResponse; 
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProductsListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $page     = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 10;

        $name     = $params['name']     ?? null;
        $category = $params['category'] ?? null;
        $price    = $params['price']    ?? null;
        $tag      = $params['tag']      ?? null;
        $status   = $params['status']   ?? null;
        $type     = $params['type']     ?? null;
        $region   = $params['region']   ?? null;
        $city     = $params['city']     ?? null;

        $paginator = $this->repository->getListOfProducts(
            $this->calculateOffset((int) $page, (int) $pageSize),
            (int) $pageSize,
            $name,
            $category,
            $price,
            $tag,
            $status,
            $type,
            $region,
            $city
        );

        return new JsonResponse(['data' => iterator_to_array($paginator), 'count' => $paginator->count()]);
    }

    private function calculateOffset(int $page, int $pageSize): int
    {
        return ($page - 1) * $pageSize;
    }
}
