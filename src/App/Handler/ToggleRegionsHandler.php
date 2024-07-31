<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\RegionsService;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ToggleRegionsHandler implements RequestHandlerInterface
{
    public function __construct(private readonly RegionsService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $regions = $request->getParsedBody()['regions'] ?? null;

        if ($regions === null) {
            return new JsonResponse([], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        $this->service->toggleList($regions);

        return new JsonResponse([], StatusCodeInterface::STATUS_OK);
    }
}
