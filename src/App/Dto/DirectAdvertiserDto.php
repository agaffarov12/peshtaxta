<?php
declare(strict_types=1);

namespace App\Dto;

class DirectAdvertiserDto
{
    public function __construct(
        public ?ClientProfile $clientProfile = null,
        public ?string $seriesAndNumber = null,
        public ?string $dateOfIssue = null,
        public ?string $authority = null,
        public ?int $inn = null,
        public ?string $dateOfBirth = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ClientProfile::fromArray($data),
            $data['seriesAndNumber'] ?? null,
            $data['dateOfIssue'] ?? null,
            $data['authority'] ?? null,
            $data['inn'] ?? null,
            $data['dateOfBirth'] ?? null,
        );
    }
}
