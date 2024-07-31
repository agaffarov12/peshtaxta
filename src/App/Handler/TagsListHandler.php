<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\TagsService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TagsListHandler implements RequestHandlerInterface
{
    public function __construct(private readonly TagsService $service)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->service->findAll());
    }
}
