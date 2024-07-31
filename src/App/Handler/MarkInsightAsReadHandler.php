<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\InsightsService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MarkInsightAsReadHandler implements RequestHandlerInterface
{
    public function __construct(private readonly InsightsService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id");

        try {
            $this->service->markAsRead($id);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['entity not found'], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse([]);
    }
}
