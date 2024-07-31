<?php
declare(strict_types=1);

namespace App\Dto;

class ProductDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $region = null,
        public ?string $city = null,
        public ?float  $width = null,
        public ?float  $height = null,
        public ?string $type = null,
        public ?string $category = null,
        public ?int    $viewingDistance = null,
        public ?int    $trafficVolume = null,
        public ?string $transportPosition = null,
        public ?string $distanceToTrafficLight = null,
        public ?string $comment = null,
        public ?LocationDto $location = null,
        public ?array $placements = null,
        public ?array $files = null,
        public ?array $tags = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? null,
            $data['region'] ?? null,
            $data['city'] ?? null,
            $data['width'] ?? null,
            $data['height'] ?? null,
            $data['type'] ?? null,
            $data['category'] ?? null,
            $data['viewingDistance'] ?? null,
            $data['trafficVolume'] ?? null,
            $data['transportPosition'] ?? null,
            $data['distanceToTrafficLight'] ?? null,
            $data['comment'] ?? null,
            LocationDto::fromArray($data['location'] ?? []),
            array_map(fn(array $placement) => ProductPlacementDto::fromArray($placement), $data['placements'] ?? []),
            $data['files'] ?? [],
            $data['tags'] ?? []
        );
    }
}
