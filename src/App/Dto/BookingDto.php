<?php
declare(strict_types=1);

namespace App\Dto;

class BookingDto
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $client = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $placement = null,
        public readonly ?string $priority = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['client'] ?? null,
            $data['startDate'] ?? null,
            $data['endDate'] ?? null,
            $data['placement'] ?? null,
            $data['priority'] ?? null,
        );
    }
}
