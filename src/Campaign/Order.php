<?php
declare(strict_types=1);

namespace Campaign;

use App\Doctrine\ClientIdType;
use App\Doctrine\OrderIdType;
use Client\ClientId;
use Common\Entity\Tag;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;
use Money\Money;

#[Entity]
#[Table(name: "orders")]
class Order implements JsonSerializable
{
    #[Id]
    #[Column(type: OrderIdType::NAME)]
    private OrderId $id;

    #[Column(type: ClientIdType::NAME)]
    private ClientId $clientId;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $comment;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[Embedded(class: Money::class)]
    private Money $price;

    #[OneToMany(mappedBy: 'order', targetEntity: Payment::class, cascade: ['persist'], fetch: "EAGER")]
    private Collection $payments;

    #[JoinTable(name: 'order_tags')]
    #[JoinColumn(name: 'order_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    #[ManyToMany(targetEntity: Tag::class, cascade: ['persist', 'remove'])]
    private Collection $tags;

    #[JoinTable(name: 'order_campaigns')]
    #[JoinColumn(name: 'order_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'campaign_id', referencedColumnName: 'id', unique: true)]
    #[ManyToMany(targetEntity: Campaign::class, cascade: [], indexBy: "id")]
    private Collection $campaigns;

    #[JoinTable(name: 'order_services')]
    #[JoinColumn(name: 'order_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'service_id', referencedColumnName: 'id')]
    #[ManyToMany(targetEntity: Service::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $services;

    public function __construct(
        ClientId          $clientId,
        array             $campaigns,
        array             $tags,
        array             $services,
        Money             $price,
        string            $comment = null
    )
    {
        $this->id        = OrderId::generate();
        $this->clientId  = $clientId;
        $this->comment   = $comment;
        $this->price     = $price;
        $this->createdAt = (new DateTimeImmutable())->setTime(0,0);
        $this->payments  = new ArrayCollection();
        $this->campaigns = new ArrayCollection($campaigns);
        $this->tags      = new ArrayCollection($tags);
        $this->services  = new ArrayCollection($services);
    }

    public function addCampaign(Campaign $campaign): void
    {
        $this->campaigns->add($campaign);
    }

    public function addPayment(Payment $payment): void
    {
        $this->payments->add($payment);
    }

    public function getId(): OrderId
    {
        return $this->id;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getClientId(): ClientId
    {
        return $this->clientId;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getTags(): array
    {
        return $this->tags->toArray();
    }

    public function getServices(): array
    {
        return $this->services->toArray();
    }

    public function getCampaigns(): Collection
    {
        return $this->campaigns;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCampaignById(string $id): Campaign
    {
        return $this->campaigns->get($id);
    }

    public function getServiceById(string $id): ?Service
    {
        foreach ($this->services as $service) {
            if ((string) $service->getId() === $id) {
                return $service;
            }
        }

        return null;
    }

    public function isPaid(): bool
    {
        $price = Money::UZS(0);

        /** @var Payment $payment */
        foreach($this->payments as $payment) {
            $price = $price->add($payment->getPrice());
        }

        return $price->greaterThanOrEqual($this->price);
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function setServices(array $services): void
    {
        $this->services = new ArrayCollection($services);
    }

    public function recalculateThePrice(): void
    {
        $price = 0;

        /** @var Campaign $campaign */
        foreach ($this->campaigns as $campaign) {
            if ($campaign->isDeleted()) {
                continue;
            }

            $price += $campaign->getPrice()->getAmount();
        }

        $this->price = Money::UZS($price);
    }

    public function jsonSerialize(): array
    {
        return [
            'id'        => (string)$this->id,
            'payments'  => $this->payments->toArray(),
            'tags'      => $this->tags->toArray(),
            'price'     => $this->price,
            'createdAt' => $this->createdAt->format(DateTimeInterface::RFC3339),
            'services' => $this->services->toArray(),
            "comment"   => $this->comment,
        ];
    }
}
