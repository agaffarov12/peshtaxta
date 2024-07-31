<?php
declare(strict_types=1);

namespace App\Dto;

class CompanyDto
{
    public function __construct(
        public ?ClientProfile $clientProfile = null,
        public ?string $name = null,
        public ?string $address = null,
        public ?string $mainBank = null,
        public ?int $mfo = null,
        public ?string $mainXr = null,
        public ?int $inn = null,
        public ?string $okonx = null,
        public ?string $additionalBank = null,
        public ?string $additionalMfo = null,
        public ?string $additionalXr = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ClientProfile::fromArray($data),
            $data['name'] ?? null,
            $data['address'] ?? null,
            $data['mainBank'] ?? null,
            $data['mfo'] ?? null,
            $data['mainXr'] ?? null,
            $data['inn'] ?? null,
            $data['okonx'] ?? null,
            $data['additionalBank'] ?? null,
            $data['additionalMfo'] ?? null,
            $data['additionalXr'] ?? null
        );
    }
}
