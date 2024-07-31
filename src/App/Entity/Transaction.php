<?php
declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use JsonSerializable;
use Money\Money;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity]
class Transaction implements JsonSerializable
{
    #[Id]
    #[Column(type: UuidType::NAME)]
    private UuidInterface $id;

    #[Embedded(class: Money::class)]
    private Money $amount;

    #[Column(type: Types::STRING, enumType: TransactionType::class)]
    private TransactionType $type;

    #[ManyToOne(targetEntity: Account::class)]
    #[JoinColumn(name: 'payment_type_id', referencedColumnName: 'id')]
    private Account $account;

    #[ManyToOne(targetEntity: TransactionCategory::class, cascade: ['persist'])]
    #[JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true)]
    private ?TransactionCategory $category;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $date;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $comment;

    public function __construct(
        TransactionType $type,
        Money $amount,
        Account $account,
        DateTimeImmutable $date,
        ?TransactionCategory $category,
        ?string $comment
    ) {
        $this->id = Uuid::uuid4();
        $this->type     = $type;
        $this->amount   = $amount;
        $this->account  = $account;
        $this->category = $category;
        $this->date     = $date;
        $this->comment  = $comment;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getCategory(): ?TransactionCategory
    {
        return $this->category;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'       => (string)$this->id,
            'amount'   => $this->amount,
            'type'     => $this->type->value,
            'account'  => ['id' => (string)$this->account->getId(), 'name' => $this->account->getName()],
            'category' => $this->category,
            'date'     => $this->date->format(DateTimeInterface::RFC3339),
            'comment'  => $this->comment,
        ];
    }
}
