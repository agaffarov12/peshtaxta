<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\OrderRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OrderDetailsHandler implements RequestHandlerInterface
{
    public function __construct(private readonly OrderRepository $repository)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id", null);

        if ($id === null) {
            return new JsonResponse([], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        try {
            $data = $this->repository->getOrderDetails($id);

        } catch (EntityNotFoundException | NonUniqueResultException $e) {
            return new JsonResponse(['entity not found'], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse($data);
    }
}
