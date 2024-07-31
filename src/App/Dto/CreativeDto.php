<?php
declare(strict_types=1);

namespace App\Dto;

use Laminas\Diactoros\UploadedFile;

class CreativeDto
{
    public function __construct(
        public readonly ?string       $id = null,
        public readonly ?string       $productPlacementId = null,
        public readonly ?UploadedFile $file = null,
        public readonly ?string       $mounted = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['placementId'] ?? null,
            $data['file'] ?? null,
            $data['mounted'] ?? null,
        );
    }
}
