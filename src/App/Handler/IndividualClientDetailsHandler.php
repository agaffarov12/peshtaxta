<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\ClientsService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndividualClientDetailsHandler implements RequestHandlerInterface
{
    public function __construct(private readonly ClientsService $service)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id", null);

        if ($id === null) {
            return new JsonResponse([], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        try {
            return new JsonResponse($this->service->getIndividualClientDetails($id));
        } catch (EntityNotFoundException $e) {
            return new JsonResponse([], StatusCodeInterface::STATUS_NOT_FOUND);
        }
    }
}
