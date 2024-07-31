<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\PaymentDto;
use App\InputFilter\OrderPaymentInputFilter;
use App\Service\OrdersService;
use App\Utils\Arrays;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddOrderPaymentHandler implements RequestHandlerInterface
{
    public function __construct(private readonly OrdersService $service, private readonly OrderPaymentInputFilter $inputFilter)
    {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = Arrays::columns(
            array_merge_recursive($request->getAttributes(), $request->getParsedBody()),
            ['order', 'price', 'type']
        );

        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        try {
            $this->service->addPayment($data['order'], PaymentDto::fromArray($data));
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
