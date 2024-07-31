<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\RegionsService;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RegionsListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly RegionsService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->service->list(), StatusCodeInterface::STATUS_OK);
    }
}