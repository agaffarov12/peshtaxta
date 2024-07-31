<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\PaymentTypesService;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreatePaymentTypeHandler implements RequestHandlerInterface
{
    public function __construct(private readonly PaymentTypesService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $name = $request->getParsedBody()['name'] ?? null;

        if ($name === null) {
            return new JsonResponse(['error' => 'name required'], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        $this->service->create($name);

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
