<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\CampaignDto;
use App\InputFilter\CampaignInputFilter;
use App\Service\CampaignsService;
use App\Utils\Arrays;
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

class EditCampaignHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly CampaignsService $service,
        private readonly CampaignInputFilter $inputFilter,
        private readonly Translator $translator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = Arrays::columns(
            array_merge_recursive($request->getAttributes(), $request->getParsedBody(), $request->getUploadedFiles()),
            ['id', 'creative', 'booking']
        );

        $this->inputFilter->setValidationGroup(['id', 'creative', 'booking']);
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
            $this->service->edit(CampaignDto::fromArray($this->inputFilter->getValues()));
        } catch (EntityNotFoundException $e) {
            return new JsonResponse([], StatusCodeInterface::STATUS_NOT_FOUND);
        } catch (BookingIntervalConflictException ) {
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
                            'bookingTooShortInterval' => $this->translator->translate('bookingIntervalTooShort')
                        ]
                    ],
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        return new JsonResponse([], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
