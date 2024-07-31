<?php
declare(strict_types=1);

namespace Campaign;

use App\Doctrine\ClientIdType;
use Client\ClientId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity]
#[Table(name: "campaign_clients")]
class CampaignClient implements JsonSerializable 
{
    #[Id]
    #[Column(type: ClientIdType::NAME)]
    private ClientId $id;

    #[Column(type: Types::STRING)]
    private string $fullName;

    public function __construct(ClientId $id, string $fullName)
    {
        $this->id = $id;
        $this->fullName = $fullName;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getId(): ClientId
    {
        return $this->id;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => (string) $this->id,
            'fullName' => $this->fullName,
        ];
    }
}
