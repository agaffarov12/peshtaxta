<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\CampaignDto;
use App\Service\CampaignsService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use App\InputFilter\CampaignInputFilter;
use Laminas\I18n\Translator\Translator;
use Product\Exception\BookingIntervalConflictException;
use Product\Exception\BookingIntervalException;
use Product\Exception\BookingIntervalTooShortException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateCampaignHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly CampaignsService $service,
        private readonly CampaignInputFilter $inputFilter,
        private readonly Translator $translator,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $input = array_merge_recursive($request->getParsedBody(), $request->getUploadedFiles());

        $this->inputFilter->setData($input);

        if (!$this->inputFilter->isValid()) {
            return new JsonResponse(
                [
                    'messages' => $this->inputFilter->getMessages(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        $input = $this->inputFilter->getValues();

        try {
            $campaign = $this->service->create(CampaignDto::fromArray($input));
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(
                [
                    'messages' => [
                        'service' => [
                            'entityNotFound' => $this->translator->translate('entityNotFound')
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
        }

        return new JsonResponse(['id' => (string) $campaign->getId()], StatusCodeInterface::STATUS_ACCEPTED);
    }
}
