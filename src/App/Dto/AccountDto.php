<?php
declare(strict_types=1);

namespace App\Dto;

class AccountDto
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?array $types = null,
        public readonly ?string $balance = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'] ?? null,
            $data['types'] ?? null,
            $data['balance'] ?? null,
        );
    }
}
