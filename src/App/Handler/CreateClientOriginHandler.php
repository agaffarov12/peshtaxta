<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\ClientOriginService;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CreateClientOriginHandler implements RequestHandlerInterface
{
    public function __construct(private readonly ClientOriginService $service)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $name = $request->getParsedBody()['name'] ?? null;

        if ($name === null) {
            return new JsonResponse(['error' => 'name required'], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        $this->service->create(trim($name));

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}