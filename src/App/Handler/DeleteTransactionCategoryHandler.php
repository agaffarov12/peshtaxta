<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\TransactionCategoriesService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteTransactionCategoryHandler implements RequestHandlerInterface
{
    public function __construct(private readonly TransactionCategoriesService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id");

        try {
            $this->service->delete($id);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(["error" => $e->getMessage()], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        return new JsonResponse([], StatusCodeInterface::STATUS_OK);
    }
}
