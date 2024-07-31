<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;
use Doctrine\DBAL\Types\Types;
use JsonSerializable;

#[Entity]
#[Table(name: 'client_origins')]
class ClientOrigin implements JsonSerializable
{
    #[Id]
    #[Column(type: UuidType::NAME)]
    private UuidInterface $id;

    #[Column(type: Types::STRING)]
    private string $name;

    #[Column(type: Types::BOOLEAN)]
    private bool $disabled;

    public function __construct(string $name)
    {
        $this->id       = Uuid::uuid4();
        $this->name     = $name;
        $this->disabled = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function disable(): void
    {
        $this->disabled = true;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'   => (string)$this->id,
            'name' => $this->name,
            'disabled' => $this->disabled,
        ];
    }
}
