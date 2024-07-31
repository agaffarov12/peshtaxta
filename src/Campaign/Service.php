<?php
declare(strict_types=1);

namespace Campaign;

use App\Doctrine\ServiceIdType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;
use Money\Money;

#[Entity]
#[Table(name: 'order_service')]
class Service implements JsonSerializable
{
    #[Id]
    #[Column(type: ServiceIdType::NAME)]
    private ServiceId $id;

    #[Column(type: Types::STRING)]
    private string $name;

    #[Embedded(class: Money::class)]
    private Money $price;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $comment;

    public function __construct(string $name, Money $price, ?string $comment = null)
    {
        $this->id      = ServiceId::generate();
        $this->name    = $name;
        $this->price   = $price;
        $this->comment = $comment;
    }

    public function getId(): ServiceId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'      => (string) $this->id,
            'name'    => $this->name,
            'price'   => $this->price,
            'comment' => $this->comment
        ];
    }
}
