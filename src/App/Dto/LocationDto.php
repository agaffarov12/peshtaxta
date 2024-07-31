<?php
declare(strict_types=1);

namespace App\Dto;

class LocationDto
{
    public function __construct(
        public ?string $latitude = null,
        public ?string $longitude = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self($data['latitude'] ?? null, $data['longitude'] ?? null);
    }
}
