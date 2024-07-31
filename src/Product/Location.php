<?php
declare(strict_types=1);

namespace Product;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use JsonSerializable;

#[Embeddable]
class Location implements JsonSerializable
{
    #[Column(type: Types::FLOAT)]
    private string $latitude;

    #[Column(type: Types::FLOAT)]
    private string $longitude;

    public function __construct(string $latitude, string $longitude)
    {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    public function jsonSerialize(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
