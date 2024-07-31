<?php
declare(strict_types=1);

namespace Campaign;

use App\Doctrine\PaymentIdType;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;
use Money\Money;

#[Entity]
#[Table(name: "payments")]
class Payment implements JsonSerializable
{
    #[Id]
    #[Column(type: PaymentIdType::NAME)]
    private PaymentId $id;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $date;

    #[Embedded(class: Money::class)]
    private Money $price;

    #[ManyToOne(targetEntity: PaymentType::class)]
    private PaymentType $type;

    #[ManyToOne(targetEntity: Order::class, inversedBy: 'payments')]
    private Order $order;

    public function __construct(Money $price, PaymentType $type, Order $order)
    {
        $this->id    = PaymentId::generate();
        $this->price = $price;
        $this->type  = $type;
        $this->date  = new DateTimeImmutable();
        $this->order = $order;
    }

    public function getId(): PaymentId
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getType(): PaymentType
    {
        return $this->type;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'    => (string)$this->id,
            'date'  => $this->date->format(DateTimeInterface::RFC3339),
            'price' => $this->price,
            'type'  => $this->type->getName(),
        ];
    }
}
