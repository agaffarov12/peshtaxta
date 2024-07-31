<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\BookingDto;
use App\InputFilter\ExtendCampaignInputFilter;
use App\Service\OrdersService;
use CannotExtendCloseCampaignException;
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

class ExtendCampaignHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly OrdersService $service,
        private readonly ExtendCampaignInputFilter $inputFilter,
        private readonly Translator $translator
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute("id");

        $this->inputFilter->setData($request->getParsedBody());

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        $values = $this->inputFilter->getValues();

        try {
            $id = (string) $this->service->extendCampaign($id, $values['campaignId'], BookingDto::fromArray($values['booking']));
        } catch (CannotExtendCloseCampaignException $e) {
            return new JsonResponse(
                [
                    'messages' => [
                        'service' => [
                            'cannotExtendNonActiveCampaign' => "cannot extend not active campaign"
                        ]
                    ],
                ],
                StatusCodeInterface::STATUS_NOT_FOUND
            );
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(
                [
                    'messages' => [
                        'service' => [
                            'entity not found' => $this->translator->translate('entityNotFound')
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

        return new JsonResponse(['id' => $id]);
    }
}
