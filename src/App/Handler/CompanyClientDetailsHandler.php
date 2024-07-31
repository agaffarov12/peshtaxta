<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\ClientsService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;

class CompanyClientDetailsHandler implements RequestHandlerInterface
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
            return new JsonResponse($this->service->getCompanyClientDetails($id));
        } catch(EntityNotFoundException $e) {
            return new JsonResponse([$e->getMessage()], StatusCodeInterface::STATUS_NOT_FOUND);
        }
    }
}
