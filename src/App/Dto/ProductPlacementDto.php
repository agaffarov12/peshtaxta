<?php
declare(strict_types=1);

namespace App\Dto;

class ProductPlacementDto
{
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?MoneyDto $price = null,
        public ?array $images = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'] ?? null,
            MoneyDto::fromArray($data['price']),
            $data['images'] ?? null,
        );
    }
}
