<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\CampaignRepository;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CampaignDetailsHandler implements RequestHandlerInterface
{
    public function __construct(private readonly CampaignRepository $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id", null);

        if ($id === null) {
            return new JsonResponse([], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        try {
            return new JsonResponse($this->service->getCampaignDetails($id));
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(['entity not found'], 404);
        }
    }
}
