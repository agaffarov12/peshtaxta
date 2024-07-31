<?php
declare(strict_types=1);

namespace App\Dto;

class OrderDto
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $clientId,
        public readonly ?MoneyDto $price,
        public readonly ?string $comment,
        public readonly ?array $campaigns,
        public readonly ?array $tags,
        public readonly ?array $services,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['clientId'] ?? null,
            MoneyDto::fromArray($data['price'] ?? []),
            $data['comment'] ?? null,
            $data['campaigns'] ?? [],
            $data['tags'] ?? [],
            array_map(fn(array $service) => ServiceDto::fromArray($service), $data['services'])
        );
    }
}
