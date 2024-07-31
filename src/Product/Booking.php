<?php
declare(strict_types=1);

namespace Product;

use App\Doctrine\BookingIdType;
use App\Doctrine\ClientIdType;
use App\Utils\Date;
use Client\ClientId;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity]
#[Table(name: "bookings")]
class Booking implements JsonSerializable
{
    #[Id]
    #[Column(type: BookingIdType::NAME)]
    private BookingId $id;

    #[Column(type: ClientIdType::NAME)]
    private ClientId $client;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $startDate;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $endDate;

    #[Column(type: Types::STRING, enumType: BookingPriority::class)]
    private BookingPriority $priority;

    #[Column(type: Types::STRING, enumType: BookingStatus::class)]
    private BookingStatus $status;

    #[ManyToOne(targetEntity: ProductPlacement::class)]
    #[JoinColumn(name: 'placement_id', referencedColumnName: 'id')]
    private ProductPlacement $placement;

    public function __construct(
        ClientId          $client,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ProductPlacement  $placement,
        BookingPriority   $priority,
    ) {
        $this->id        = BookingId::generate();
        $this->client    = $client;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->placement = $placement;
        $this->priority  = $priority;
        $this->status    = BookingStatus::DEFAULT;
    }

    public function getId(): BookingId
    {
        return $this->id;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getClient(): ClientId
    {
        return $this->client;
    }

    public function getPlacement(): ProductPlacement
    {
        return $this->placement;
    }

    public function getPriority(): BookingPriority
    {
        return $this->priority;
    }

    public function getStatus(): BookingStatus
    {
        return $this->status;
    }

    public function setPriority(BookingPriority $priority): void
    {
        $this->priority = $priority;
    }

    public function setStatus(BookingStatus $status): void
    {
        $this->status = $status;
    }

    public function setStartDate(DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function setEndDate(DateTimeImmutable $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function setPlacement(ProductPlacement $placement): void
    {
        $this->placement = $placement;
    }

    public function cancel(): void
    {
        $this->status = BookingStatus::CANCELLED;
    }

    public function isMediumAndNotExtended(): bool
    {
        return $this->priority === BookingPriority::MEDIUM && $this->getStatus() === BookingStatus::EXTENDED;
    }

    public function calculateThePrice(): int
    {
        $placementPrice = $this->placement->getPrice()->getAmount();
        $days = Date::getDateDifferenceInDays($this->startDate, $this->endDate);

        return $placementPrice * $days;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => (string) $this->id,
            'client_id' => (string) $this->client,
            'startDate' => $this->startDate->format(DateTimeInterface::RFC3339),
            'endDate' => $this->endDate->format(DateTimeInterface::RFC3339),
            'priority' => $this->priority->value,
            'placement' => (string) $this->placement->getId(),
        ];
    }
}
