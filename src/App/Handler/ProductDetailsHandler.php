<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\ProductRepository;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProductDetailsHandler implements RequestHandlerInterface
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id");

        if ($id === null) {
            return new JsonResponse([], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        try {
            $product = $this->repository->findById($id);
        } catch(EntityNotFoundException $e) {
            return new JsonResponse([], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $productSerialized = $product->jsonSerialize();

        $productSerialized['bookings'] = $product->getBookings();
        
        return new JsonResponse($productSerialized);
    }
}
