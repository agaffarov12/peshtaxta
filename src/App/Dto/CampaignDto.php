<?php
declare(strict_types=1);

namespace App\Dto;

class CampaignDto
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $status,
        public readonly ?string $clientId,
        public readonly ?string $productId,
        public readonly ?BookingDto $booking,
        public readonly ?CreativeDto $creative,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            null,
            $data['clientId'] ?? null,
            $data['productId'] ?? null,
            BookingDto::fromArray($data['booking'] ?? []),
            CreativeDto::fromArray($data['creative'] ?? []),
        );
    }
}
