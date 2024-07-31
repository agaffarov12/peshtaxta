<?php
declare(strict_types=1);

namespace App\Dto;

class ServiceDto
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?MoneyDto $price = null,
        public readonly ?string $comment = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'] ?? null,
            MoneyDto::fromArray($data['price']),
            $data['comment'] ?? null,
        );
    }
}
