<?php
declare(strict_types=1);

namespace Campaign;

use App\Entity\Account;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity]
#[Table(name: 'payment_types')]
class PaymentType implements \JsonSerializable
{
    #[Id]
    #[Column(type: UuidType::NAME)]
    private UuidInterface $id;

    #[Column(type: Types::STRING)]
    private string $name;

    #[Column(type: Types::BOOLEAN)]
    private bool $disabled;

    #[ManyToOne(targetEntity: Account::class, inversedBy: 'paymentTypes')]
    #[JoinColumn(name: 'account_id', referencedColumnName: 'id')]
    private ?Account $account;

    public function __construct(string $name)
    {
        $this->id       = Uuid::uuid4();
        $this->name     = $name;
        $this->disabled = false;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setAccount(?Account $account): void
    {
        $this->account = $account;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function disable(): void
    {
        $this->disabled = true;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'   => (string)$this->id,
            'name' => $this->name,
            'disabled' => $this->disabled,
            'account' => $this->account ? (string) $this->account->getId() : null
        ];
    }
}
