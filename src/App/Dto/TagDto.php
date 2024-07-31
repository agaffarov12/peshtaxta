<?php
declare(strict_types=1);

namespace App\Dto;

class TagDto
{
    public function __construct(
        public ?string $id = null,
        public ?string $value = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['value'] ?? null,
        );
    }

}
