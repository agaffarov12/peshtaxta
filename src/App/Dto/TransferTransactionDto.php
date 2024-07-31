<?php
declare(strict_types=1);

namespace App\Dto;

class TransferTransactionDto
{
    public function __construct(
        public readonly ?MoneyDto $amount = null,
        public readonly ?string $fromAccount = null,
        public readonly ?string $toAccount = null,
        public readonly ?string $date = null,
        public readonly ?string $comment = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            MoneyDto::fromArray($data['amount'] ?? []),
            $data['fromAccount'] ?? null,
            $data['toAccount'] ?? null,
            $data['date'] ?? null,
            $data['comment'] ?? null
        );
    }
}
