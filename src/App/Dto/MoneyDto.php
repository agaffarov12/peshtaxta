<?php
declare(strict_types=1);

namespace App\Dto;

class MoneyDto
{
    public function __construct(
        public readonly ?int $amount = null,
        public readonly ?string $currency = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['amount']) ? (int) $data['amount'] : null,
            $data['currency'] ?? null
        );
    }
}
