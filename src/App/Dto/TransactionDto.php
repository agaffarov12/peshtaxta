<?php
declare(strict_types=1);

namespace App\Dto;

class TransactionDto
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $type = null,
        public readonly ?MoneyDto $amount = null,
        public readonly ?string $account = null,
        public readonly ?string $category = null,
        public readonly ?string $date = null,
        public readonly ?string $comment = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['type'] ?? null,
            MoneyDto::fromArray($data['amount'] ?? []),
            $data['account'] ?? null,
            $data['category'] ?? null,
            $data['date'] ?? null,
            $data['comment'] ?? null,
        );
    }
}
