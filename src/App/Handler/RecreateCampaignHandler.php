<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\CampaignDto;
use App\InputFilter\CampaignInputFilter;
use App\Service\CampaignsService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\I18n\Translator\Translator;
use Product\Exception\BookingIntervalConflictException;
use Product\Exception\BookingIntervalException;
use Product\Exception\BookingIntervalTooShortException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RecreateCampaignHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly CampaignsService $service,
        private readonly CampaignInputFilter $inputFilter,
        private readonly Translator $translator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setValidationGroup("id", "clientId", "productId", "booking");
        $this->inputFilter->setData($request->getParsedBody());

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        try {
            $id = $this->service->recreateCampaign(CampaignDto::fromArray($this->inputFilter->getValues()));
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
                            'bookingIntervalConflict' => $this->translator->translate('bookingIntervalConflict')
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
                            'bookingShortInterval' => $this->translator->translate('bookingShortInterval')
                        ]
                    ],
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        } catch (BookingIntervalTooShortException $e) {
            return new JsonResponse(
                [
                    'messages' => [
                        'service' => [
                            'bookingIntervalTooShort' => $this->translator->translate('bookingIntervalTooShort')
                        ]
                    ],
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        return new JsonResponse(['id' => (string) $id], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
