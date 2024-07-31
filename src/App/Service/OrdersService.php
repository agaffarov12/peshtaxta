<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\BookingDto;
use App\Dto\CampaignDto;
use App\Dto\CampaignView;
use App\Dto\OrderDto;
use App\Dto\PaymentDto;
use App\Dto\ProductPlacementDto;
use App\Dto\ServiceDto;
use App\Dto\TransactionDto;
use App\Exception\NotEnoughMoneyException;
use App\Utils\Date;
use Campaign\Campaign;
use Campaign\CampaignId;
use Campaign\CampaignStatus;
use Campaign\Order;
use Campaign\OrderId;
use Campaign\Payment;
use Campaign\PaymentType;
use Campaign\Service;
use CannotExtendCloseCampaignException;
use Client\ClientId;
use DateTimeImmutable;
use Doctrine\ORM\EntityNotFoundException;
use Money\Currency;
use Money\Money;
use Product\Exception\BookingIntervalConflictException;
use Product\Exception\BookingIntervalException;
use Product\Exception\BookingIntervalTooShortException;

class OrdersService
{
    public function __construct(
        private readonly OrderRepository $repository,
        private readonly CampaignRepository $campaignRepository,
        private readonly CampaignsService $campaignsService,
        private readonly TagsService $tagsService,
        private readonly PaymentTypesService $paymentTypesService,
        private readonly TransactionsService $transactionsService,
        private readonly ClientsService $clientsService,
    ) {
    }

    /**
     * @throws EntityNotFoundException
     * @throws NotEnoughMoneyException
     */
    public function addPayment(OrderId | string $id, PaymentDto $dto): void
    {
        $order = $this->repository->findById($id);
        $paymentType = $this->paymentTypesService->get($dto->type);
        $money = new Money($dto->price->amount, new Currency($dto->price->currency));

        $order->addPayment(
            new Payment(
                $money,
                $paymentType,
                $order
            )
        );

        $this->transactionsService->createWithPaymentType($paymentType, $money);

        if ($order->isPaid()) {
            $this->activateCampaigns((string)$order->getId());
            $this->clientsService->checkForDebts((string) $order->getClientId());
        }

        $this->repository->save($order);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function create(OrderDto $dto): OrderId
    {
        $campaigns = $this->campaignRepository->findWithIds($dto->campaigns);

        $tags = empty($dto->tags) ? [] : $this->tagsService->addTags($dto->tags);

        $services = $dto->services ? $this->mapServices($dto->services) : [];

        $order = new Order(
            ClientId::fromString($dto->clientId),
            $campaigns,
            $tags,
            $services,
            new Money($dto->price->amount, new Currency($dto->price->currency)),
            $dto->comment
        );

        /* @var Campaign $campaign  */
        foreach ($campaigns as $campaign) {
            $campaign->setStatus(CampaignStatus::RESERVED);
            $campaign->setOrderId($order->getId());

            $this->campaignRepository->add($campaign);
        }

        $this->repository->save($order);
        $this->clientsService->setToIndebted($dto->clientId);

        return $order->getId();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function edit(string $id, OrderDto $dto): void
    {
        $order = $this->repository->findById($id);

        $this->editServices($dto->services, $order);

        foreach($dto->campaigns as $campaign) {
            if ($campaign['cancelled'] === false) {
                continue;
            }

            $campaign = $order->getCampaignById($campaign['id']);

            $this->campaignsService->cancel($campaign);
        }

        $order->recalculateThePrice();

        $this->repository->save($order);
    }

    private function editServices(array $services, Order &$order): void
    {
        $orderServices = [];

        /** @var ServiceDto $s */
        foreach($services as $s) {
            if ($s->id !== null) {
                $service = $order->getServiceById($s->id);

                $service->setName($s->name);
                $service->setPrice(new Money($s->price->amount, new Currency($s->price->currency)));
                $service->setComment($s->comment);

                $orderServices[] = $service;
            } else {
                $orderServices[] = new Service(
                    $s->name,
                    new Money($s->price->amount, new Currency($s->price->currency)),
                    $s->comment
                );
            }
        }

        $order->setServices($orderServices);
    }

    /**
     * @throws BookingIntervalConflictException
     * @throws BookingIntervalTooShortException
     * @throws BookingIntervalException
     * @throws EntityNotFoundException
     */
    public function createWithCampaign(OrderDto $orderDto, CampaignDto $campaignDto): OrderId
    {
        $campaign = $this->campaignsService->create($campaignDto);

        $order = new Order(
            ClientId::fromString($orderDto->clientId),
            [$campaign],
            $orderDto->tags,
            [],
            new Money($orderDto->price->amount, new Currency($orderDto->price->currency)),
            $orderDto->comment
        );

        $this->repository->save($order);

        return $order->getId();
    }

    /**
     * @throws BookingIntervalConflictException
     * @throws CannotExtendCloseCampaignException
     * @throws BookingIntervalException
     * @throws EntityNotFoundException
     * @throws BookingIntervalTooShortException
     */
    public function extendCampaign(string $orderId, string $campaignId, BookingDto $bookingDto): OrderId
    {
        $order    = $this->repository->findById($orderId);
        $campaign = $order->getCampaignById($campaignId);

        $newCampaign = $this->campaignsService->extendCampaign($campaign, $bookingDto);
        $price       = $this->calculatePrice((string) $newCampaign->getId());

        $newOrder = new Order(
            $order->getClientId(),
            [$newCampaign],
            $order->getTags(),
            $order->getServices(),
            Money::UZS($price)->subtract($order->getPrice()),
            $order->getComment()
        );

        $newCampaign->setOrderId($newOrder->getId());
        $newOrder->addCampaign($newCampaign);

        $this->repository->save($newOrder);

        return $newOrder->getId();
    }

    /**
     * @throws EntityNotFoundException
     */
    private function calculatePrice(string $campaignId): int  {
        /** @var CampaignView $details */
        $details = $this->campaignRepository->getCampaignDetails($campaignId);

        $days = Date::getDateDifferenceInDays($details->startDate, $details->endDate);

        return $details->placementPrice * $days;
    }

    public function activateCampaigns(string $id): void
    {
        $campaigns = $this->campaignRepository->getCampaignsWithOrder($id);

        $now = new DateTimeImmutable();

        foreach ($campaigns as $c) {
            /** @var Campaign $campaign */
            $campaign = $c[0];

            if ($now >= $c['startDate'] && $now < $c['endDate'] && $campaign->getStatus() === CampaignStatus::RESERVED) {
                $campaign->setStatus(CampaignStatus::ACTIVE);
                $this->campaignRepository->add($campaign);
            }
        }
    }

    private function mapServices(array $services): array
    {
        return array_map(
            fn(ServiceDto $service) => new Service(
                $service->name,
                new Money($service->price->amount, new Currency($service->price->currency))
            ),
            $services
        );
    }
}
