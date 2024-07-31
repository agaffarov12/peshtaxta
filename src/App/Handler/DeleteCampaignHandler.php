<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\CampaignsService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class DeleteCampaignHandler implements RequestHandlerInterface
{
    public function __construct(private readonly CampaignsService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id");

        if (!$id || !Uuid::isValid($id)) {
            return new JsonResponse([], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        try {
            $this->service->delete($id);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['error' => "Entity not found"], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
