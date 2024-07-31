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

class MountBannerHandler implements RequestHandlerInterface
{
    public function __construct(private readonly CampaignsService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id");

        try {
            $this->service->toggleBanner($id);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse([], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse(['success']);
    }
}
