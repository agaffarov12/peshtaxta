<?php
declare(strict_types=1);

namespace App\Entity;

use App\Exception\NotEnoughMoneyException;
use Campaign\PaymentType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use JsonSerializable;
use Money\Money;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity]
class Account implements JsonSerializable
{
    #[Id]
    #[Column(type: UuidType::NAME)]
    private UuidInterface $id;

    #[Column(type: Types::STRING)]
    private string $name;

    #[OneToMany(mappedBy: 'account', targetEntity: PaymentType::class)]
    private Collection $paymentTypes;

    #[Embedded(class: Money::class)]
    private Money $balance;

    #[Column(type: Types::BOOLEAN)]
    private bool $disabled;

    public function __construct(string $name, array $paymentTypes, ?Money $balance = null)
    {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->disabled = false;
        $this->balance = $balance ?? Money::UZS(0);
        $this->paymentTypes = new ArrayCollection($paymentTypes);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPaymentTypes(): Collection
    {
        return $this->paymentTypes;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setPaymentTypes(array $types): void
    {
        $this->paymentTypes = new ArrayCollection($types);
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function disable(): void
    {
        $this->disabled = true;
    }

    /**
     * @throws NotEnoughMoneyException
     */
    public function withdrawMoney(Money $amount): void
    {
        if ($this->balance->lessThan($amount)) {
            throw new NotEnoughMoneyException();
        }
        $this->balance = $this->balance->subtract($amount);
    }

    public function addMoney(Money $amount): void
    {
        $this->balance = $this->balance->add($amount);
    }

    /**
     * @throws NotEnoughMoneyException
     */
    public function processTransaction(Transaction $transaction): void
    {
        if ($transaction->getType() === TransactionType::INCOME) {
            $this->addMoney($transaction->getAmount());
        } else {
            $this->withdrawMoney($transaction->getAmount());
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'types' => $this->paymentTypes->toArray(),
            'balance' => $this->balance,
            'disabled' => $this->disabled,
        ];
    }
}
