<?php
declare(strict_types=1);

namespace App\Dto;

use App\Entity\ClientType;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;

class OrderView implements JsonSerializable
{
    public function __construct(
        public readonly string            $id,
        public readonly string            $clientId,
        public readonly ClientType        $clientType,
        public readonly string            $clientFirstName,
        public readonly string            $clientLastName,
        public readonly DateTimeImmutable $createdAt,
        public readonly int               $price,
        public readonly ?int              $paidPrice,
        public ?int                       $balanceForToday = null,
        public ?int                       $balanceForAllTime = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id'                => $this->id,
            'clientId'          => $this->clientId,
            'clientType'        => $this->clientType->value,
            'clientFirstName'   => $this->clientFirstName,
            'clientLastName'    => $this->clientLastName,
            'createdAt'         => $this->createdAt->format(DateTimeInterface::RFC3339),
            'price'             => $this->price,
            'paidPrice'         => $this->paidPrice ?? 0,
            'balanceForAllTime' => $this->balanceForAllTime,
            'balanceForToday'   => $this->balanceForToday,
        ];
    }
}
