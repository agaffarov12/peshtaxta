<?php
declare(strict_types=1);

namespace App\Dto;

use App\Entity\Transaction;

class PaymentDto
{
    public function __construct(
        public readonly ?string         $id = null,
        public readonly ?string         $type = null,
        public readonly ?MoneyDto       $price = null,

    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['type'] ?? null,
            MoneyDto::fromArray($data['price'] ?? []),
        );
    }
}
