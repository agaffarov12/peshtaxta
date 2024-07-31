<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\CampaignRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CampaignsListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly CampaignRepository $service)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $page            = $params['page'] ?? 1;
        $pageSize        = $params['pageSize'] ?? 10;
        $product         = $params['product'] ?? null;
        $client          = $params['client'] ?? null;
        $order           = $params['order'] ?? null;
        $status          = $params['status'] ?? null;
        $clientFirstName = $params['clientFirstName'] ?? null;
        $clientLastName  = $params['clientLastName'] ?? null;
        $productName     = $params['productName'] ?? null;
        $nonClosed       = $params['nonClosed'] ?? false;

        $paginator = $this->service->list(
            $this->calculateOffset((int)$page, (int)$pageSize),
            (int)$pageSize,
            $product,
            $client,
            $order,
            $status,
            $clientFirstName,
            $clientLastName,
            $productName,
            (bool) $nonClosed
        );
        $paginator->setUseOutputWalkers(false);

        return new JsonResponse(['data' => iterator_to_array($paginator), 'count' => $paginator->count()]);
    }

    private function calculateOffset(int $page, int $pageSize)
    {
        return ($page - 1) * $pageSize;
    }
}
