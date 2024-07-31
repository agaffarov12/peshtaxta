<?php
declare(strict_types=1);

namespace App\Handler;

use App\Dto\CampaignDto;
use App\Dto\OrderDto;
use App\InputFilter\OrderWithCampaignInputFilter;
use App\Service\OrdersService;
use Doctrine\ORM\EntityNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Product\Exception\BookingIntervalConflictException;
use Product\Exception\BookingIntervalException;
use Product\Exception\BookingIntervalTooShortException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateCampaignWithOrderHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly OrdersService $service,
        private readonly OrderWithCampaignInputFilter $inputFilter
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

        $order = OrderDto::fromArray($this->inputFilter->getValues());
        $campaign = CampaignDto::fromArray($this->inputFilter->getValues()['campaign']);

        try {
            $this->service->createWithCampaign($order, $campaign);
        } catch (EntityNotFoundException $e) {

        } catch (BookingIntervalConflictException $e) {

        } catch (BookingIntervalException $e) {

        } catch (BookingIntervalTooShortException $e) {

        }

        return new JsonResponse([]);
    }
}
