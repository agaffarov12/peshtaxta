<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\InsightsService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InsightsListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly InsightsService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $context = $params['context'] ?? null;

        return new JsonResponse($this->service->list($context));
    }
}
