<?php
declare(strict_types=1);

namespace App\Handler;

use App\Entity\StatisticsInterval;
use App\Service\CampaignRepository;
use App\Service\OrderRepository;
use App\Service\ClientsService;
use App\Service\StatisticsService;
use App\Utils\Date;
use DateTimeImmutable;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StatisticsHandler implements RequestHandlerInterface
{
    public function __construct(private readonly StatisticsService $service)
    {}

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

        return new JsonResponse($this->service->getStatistics($startDate, $endDate));
    }
}
