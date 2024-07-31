<?php
declare(strict_types=1);

namespace App\Dto;

class ClientProfile
{
    public function __construct(
        public ?string         $id = null,
        public ?string         $firstName = null,
        public ?string         $lastName = null,
        public ?string         $surname = null,
        public ?ContactDetails $contactDetails = null,
        public ?string         $comment = null,
        public ?string         $type = null,
        public ?string         $category = null,
        public ?string         $origin = null,
        public ?array          $tags = null,
        public ?array          $files = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['firstName'] ?? null,
            $data['lastName'] ?? null,
            $data['surname'] ?? null,
            ContactDetails::fromArray($data['contactDetails'] ?? []),
            $data['comment'] ?? null,
            $data['type'] ?? null,
            $data['category'] ?? null,
            $data['origin'] ?? null,
            $data['tags'] ?? [],
            $data['files'] ?? []
        );
    }
}
