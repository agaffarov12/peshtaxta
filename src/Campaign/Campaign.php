<?php
declare(strict_types=1);

namespace Campaign;

use App\Doctrine\BookingIdType;
use App\Doctrine\CampaignIdType;
use App\Doctrine\ClientIdType;
use App\Doctrine\OrderIdType;
use App\Doctrine\ProductIdType;
use Client\ClientId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;
use Money\Money;
use Product\BookingId;
use Product\ProductId;

#[Entity]
#[Table(name: "Campaigns")]
class Campaign implements JsonSerializable
{
    #[Id]
    #[Column(type: CampaignIdType::NAME)]
    private CampaignId $id;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[Column(type: Types::STRING, enumType: CampaignStatus::class)]
    private CampaignStatus $status;

    #[Embedded(class: Money::class)]
    private Money $price;

    #[Column(type: BookingIdType::NAME)]
    private BookingId $bookingId;
    
    #[Column(type: ClientIdType::NAME)]
    private ClientId $clientId;

    #[Column(type: ProductIdType::NAME)]
    private ProductId $productId;

    #[Column(type: Types::BOOLEAN)]
    private bool $deleted;

    #[Column(type: OrderIdType::NAME, nullable: true)]
    private ?OrderId $orderId;

    #[ManyToOne(targetEntity: Creative::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(name: 'creative_id', referencedColumnName: 'id')]
    private Creative $creative;

    public function __construct(
        ClientId $clientId,
        ProductId $productId,
        BookingId $bookingId,
        Creative $creative,
        Money $price,
    ) {
        $this->id        = CampaignId::generate();
        $this->clientId  = $clientId;
        $this->productId = $productId;
        $this->bookingId = $bookingId;
        $this->status    = CampaignStatus::CREATED;
        $this->createdAt = (new DateTimeImmutable())->setTime(0,0);
        $this->creative  = $creative;
        $this->price     = $price;
        $this->deleted   = false;
    }

    public function getId(): CampaignId
    {
        return $this->id;
    }

    public function getStatus(): CampaignStatus
    {
        return $this->status;
    }

    public function getBookingId(): BookingId
    {
        return $this->bookingId;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getClientId(): ClientId
    {
        return $this->clientId;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getOrderId(): ?OrderId
    {
        return $this->orderId;
    }

    public function getCreative(): Creative
    {
        return $this->creative;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setOrderId(OrderId $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function setStatus(CampaignStatus $status): void
    {
        $this->status = $status;
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setBookingId(BookingId $bookingId): void
    {
        $this->bookingId = $bookingId;
    }

    public function setClientId(ClientId $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function setProductId(ProductId $productId): void
    {
        $this->productId = $productId;
    }

    public function setCreative(Creative $creative): void
    {
        $this->creative = $creative;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'        => (string) $this->id,
            'status'    => $this->status->value,
            'clientId'  => (string) $this->clientId,
            'productId' => (string) $this->productId,
            'bookingId' => (string) $this->bookingId,
            'price'     => $this->price,
        ];
    }
}
