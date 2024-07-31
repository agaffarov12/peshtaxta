<?php
declare(strict_types=1);

namespace App\Handler;

use App\Service\ClientCategoryService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteClientCategoryHandler implements RequestHandlerInterface
{
    public function __construct(private readonly ClientCategoryService $service)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $category = $request->getAttribute("category", null);

        if ($category === null) {
            return new JsonResponse(
                [
                    'messages' => [
                        'category' => [
                            'field empty' => "no category name sent"
                        ]
                    ],
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        try {
            $this->service->delete($category);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(
                [
                    'messages' => [
                        'service' => [
                            'entity not found' => "requested product is not found"
                        ]
                    ],
                ],
                StatusCodeInterface::STATUS_NOT_FOUND
            );
        }

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
