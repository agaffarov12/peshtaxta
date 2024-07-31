<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\OrderDto;
use App\InputFilter\OrderInputFilter;
use App\Service\OrdersService;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateOrderHandler implements RequestHandlerInterface
{
    public function __construct(private readonly OrdersService $service, private readonly OrderInputFilter $inputFilter)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setData($request->getParsedBody());

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        $id = $this->service->create(OrderDto::fromArray($this->inputFilter->getValues()));

        return new JsonResponse(['id' => (string) $id], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
