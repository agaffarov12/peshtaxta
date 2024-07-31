<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\StatisticsService;
use DateTimeImmutable;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProductStatisticsHandler implements RequestHandlerInterface
{
    public function __construct(private readonly StatisticsService $service)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $startDate = $params['startDate'] ?? null;
        $endDate = $params['endDate'] ?? null;

        if ($startDate !== null) {
            $startDate = new DateTimeImmutable($startDate);
        }

        if ($endDate !== null) {
            $endDate = new DateTimeImmutable($endDate);
        }

        return new JsonResponse($this->service->getProductStatistics($startDate, $endDate));
    }
}