<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\BookingDto;
use App\InputFilter\BookingInputFilter;
use App\Service\ProductRepository;
use App\Service\ProductsService;
use App\Utils\Arrays;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Product\Exception\BookingIntervalConflictException;
use Product\Exception\BookingIntervalException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddBookingHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ProductsService $service,
        private readonly BookingInputFilter $inputFilter
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = Arrays::columns(
            array_merge_recursive($request->getAttributes(), $request->getParsedBody()),
            ['product', 'client', 'placement', 'startDate', 'endDate']
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

        $data = $this->inputFilter->getValues();

        try {
            $id = $this->service->addBooking($data['product'], BookingDto::fromArray($data));
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
        } catch (BookingIntervalConflictException $e) {
            return new JsonResponse(
                [
                    'messages' => [
                        'service' => [
                            'bookingIntervalConflict' => $e->getMessage()
                        ]
                    ],
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        } catch (BookingIntervalException $e) {
            return new JsonResponse(
                [
                    'messages' => [
                        'service' => [
                            'bookingShortInterval' => $e->getMessage()
                        ]
                    ],
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        return new JsonResponse(['id' => (string) $id]);
    }
}
