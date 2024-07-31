<?php
declare(strict_types=1);

namespace App\Dto;

class ContactDetails
{
    public function __construct(
        public ?string $phoneNumbers,
        public ?string $email,
        public ?string $telegram,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['phoneNumbers'] ?? null,
            $data['email'] ?? null,
            $data['telegram'] ?? null,
        );
    }
}
