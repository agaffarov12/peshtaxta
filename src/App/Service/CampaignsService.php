<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\BookingDto;
use App\Dto\CampaignDto;
use App\Entity\Client;
use App\Entity\InsightEventType;
use App\Utils\Date;
use App\Utils\DateTimeFormatConverter;
use Campaign\Campaign;
use Campaign\CampaignClient;
use Campaign\CampaignId;
use Campaign\CampaignStatus;
use Campaign\Creative;
use CannotExtendCloseCampaignException;
use Client\ClientId;
use Common\Entity\File;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Money\Money;
use Product\Booking;
use Product\BookingId;
use Product\BookingPriority;
use Product\Exception\BookingIntervalConflictException;
use Product\Exception\BookingIntervalException;
use Product\Exception\BookingIntervalTooShortException;
use Product\Product;
use Product\ProductId;
use Product\ProductPlacement;
use Product\ProductPlacementId;

class CampaignsService 
{
    public function __construct(
        private readonly CampaignRepository     $repository,
        private readonly EntityManagerInterface $orm,
        private readonly InsightsService        $service,
        private readonly ProductsService        $productsService,
    ) {
    }

    /**
     * @throws BookingIntervalConflictException
     * @throws EntityNotFoundException
     * @throws BookingIntervalException
     * @throws BookingIntervalTooShortException
     */
    public function create(CampaignDto $dto): Campaign
    {
        $bookingDto = $dto->booking;

        $bookingId = $this->productsService->addBooking($dto->productId, $bookingDto);

        $campaign = new Campaign(
            ClientId::fromString($dto->clientId),
            ProductId::fromString($dto->productId),
            $bookingId,
            new Creative(
                ProductPlacementId::fromString($dto->creative->productPlacementId),
                new File($dto->creative->file->getStream()->getMetadata()['uri']),
                false
            ),
            Money::UZS($this->calculatePrice($dto->booking))
        );

        if ($bookingDto->priority === BookingPriority::LOW->value) {
            $this->cancelCampaignsBetween(
                DateTimeFormatConverter::toDateTime($bookingDto->startDate),
                DateTimeFormatConverter::toDateTime($bookingDto->endDate),
                $dto->productId,
                $bookingDto->placement
            );
        }

        $this->repository->add($campaign);

        return $campaign;
    }

    /**
     * @throws EntityNotFoundException
     * @throws BookingIntervalConflictException
     * @throws BookingIntervalException
     * @throws BookingIntervalTooShortException
     */
    public function edit(CampaignDto $dto): void
    {
        $campaign = $this->repository->findById($dto->id);
        $booking = $this->orm->find(Booking::class, $campaign->getBookingId());

        $this->productsService->removeBooking($campaign->getProductId(), $booking);
        $id = $this->productsService->addBooking((string) $campaign->getProductId(), $dto->booking);

        $campaign->setBookingId($id);

        //if ($campaign->getProductId() !== ProductId::fromString($dto->productId)) {
        //    $this->productsService->addBooking($dto->productId, $dto->booking);
        //    $this->productsService->removeBooking($campaign->getProductId(), $booking);
        //
        //    $campaign->setProductId(ProductId::fromString($dto->productId));
        //} else {
        //    $id = $this->productsService->editBooking($campaign->getProductId(), $dto->booking);
        //    $campaign->setBookingId($id);
        //}
        //
        //$campaign->setClientId(ClientId::fromString($dto->clientId));

        if ($dto->creative->file) {
            $campaign->setCreative(new Creative(
                ProductPlacementId::fromString($dto->creative->productPlacementId),
                new File($dto->creative->file->getStream()->getMetadata()['uri']),
                false
            ));
        }

        $this->repository->add($campaign);
    }

    /**
     * @throws BookingIntervalConflictException
     * @throws EntityNotFoundException
     * @throws BookingIntervalException
     * @throws CannotExtendCloseCampaignException
     * @throws BookingIntervalTooShortException
     */
    public function extendCampaign(Campaign $campaign, BookingDto $bookingDto): Campaign
    {
        if ($campaign->getStatus() !== CampaignStatus::ACTIVE) {
            throw new CannotExtendCloseCampaignException();
        }

        $bookingId = $this->productsService->addBooking(
            (string) $campaign->getProductId(),
            $bookingDto,
            (string) $campaign->getBookingId()
        );

        $this->productsService->cancelBookingsBetween(
            $campaign->getProductId(),
            DateTimeFormatConverter::toDateTime($bookingDto->startDate),
            DateTimeFormatConverter::toDateTime($bookingDto->endDate),
            $bookingId,
            BookingPriority::from($bookingDto->priority)
        );

        $overlappingCampaigns = $this->repository->getCampaignsBetween(
            DateTimeFormatConverter::toDateTime($bookingDto->startDate),
            DateTimeFormatConverter::toDateTime($bookingDto->endDate),
            (string) $campaign->getProductId(),
            $bookingDto->placement
        );

        /** @var Campaign $c */
        foreach($overlappingCampaigns as $c) {
            if ($c->getId() !== $campaign->getId()) {
                $c->setStatus(CampaignStatus::BOOKING_CANCELLED);
                $this->repository->add($c);
            }
        }

        $newCampaign = new Campaign(
            $campaign->getClientId(),
            $campaign->getProductId(),
            $bookingId,
            $campaign->getCreative(),
            Money::UZS($this->calculatePrice($bookingDto))
        );

        $this->repository->add($newCampaign);

        return $newCampaign;
    }

    public function show(): Paginator 
    {
        $builder = $this->orm->createQueryBuilder();

        $query = $builder
                    ->select(['campaign'])
                    ->from(Campaign::class, 'campaign')
                    ->innerJoin(Product::class, 'product', 'WITH', 'product.id = campaign.productId')
                    ->getQuery();

        return new Paginator($query);            
    }

    /**
     * @throws EntityNotFoundException
     */
    public function toggleBanner(string $id): void
    {
        $campaign = $this->repository->findById($id);

        $creative = $campaign->getCreative();

        if ($creative->isMounted()) {
            $creative->unmount();
            $this->service->markAsReadByEvent($id, InsightEventType::CAMPAIGN_END);
        } else {
            $creative->mount();
            $this->service->markAsReadByEvent($id, InsightEventType::CAMPAIGN_START);
        }

        $this->repository->add($campaign);
    }

    /**
     * @throws BookingIntervalConflictException
     * @throws BookingIntervalException
     * @throws BookingIntervalTooShortException
     * @throws EntityNotFoundException
     */
    public function recreateCampaign(CampaignDto $dto): CampaignId
    {
        $bookingId = $this->productsService->addBooking($dto->productId, $dto->booking);

        $cancelledCampaign = $this->repository->findById($dto->id);

        $newCampaign = new Campaign(
            ClientId::fromString($dto->clientId),
            ProductId::fromString($dto->productId),
            $bookingId,
            $cancelledCampaign->getCreative(),
            Money::UZS($this->calculatePrice($dto->booking))
        );

        $this->repository->add($newCampaign);

        return $newCampaign->getId();
    }

    private function cancelCampaignsBetween(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        string $productId,
        string $placementId
    ): void {
        $campaigns = $this->repository->getCampaignsBetween(
            $startDate,
            $endDate,
            $productId,
            $placementId
        );

        /** @var Campaign $c */
        foreach($campaigns as $c) {
            $c->setStatus(CampaignStatus::BOOKING_CANCELLED);
            $this->repository->add($c);
        }
    }

    /**
     * @throws EntityNotFoundException
     */
    public function delete(string $id): void
    {
        $campaign = $this->repository->findById($id);

        if ($campaign->getStatus() === CampaignStatus::ACTIVE) {
            return;
        }

        $campaign->delete();

        $this->repository->add($campaign);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function cancel(Campaign $campaign): void
    {
        if ($campaign->getStatus() === CampaignStatus::RESERVED) {
            $campaign->delete();

            $this->productsService->cancelBooking($campaign->getProductId(), $campaign->getBookingId());

            return;
        }

        $price = $this->productsService->editEndDateOfBooking(
            (string) $campaign->getProductId(),
            (string) $campaign->getBookingId()
        );

        $campaign->setPrice(Money::UZS($price));
        $campaign->setStatus(CampaignStatus::CLOSED);

        $this->repository->add($campaign);
    }

    private function calculatePrice(BookingDto $dto): int
    {
        $placement = $this->orm->find(ProductPlacement::class, ProductPlacementId::fromString($dto->placement));
        $placementPrice = $placement->getPrice()->getAmount();
        $days = Date::getDateDifferenceInDays(
            new DateTimeImmutable($dto->startDate),
            new DateTimeImmutable($dto->endDate)
        );

        return $placementPrice * $days;
    }
}
