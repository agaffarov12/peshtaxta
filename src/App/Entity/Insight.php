<?php
declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity]
#[Table(name: "Insights")]
class Insight implements JsonSerializable
{
    #[Id]
    #[Column(type: UuidType::NAME)]
    private UuidInterface $id;

    #[Column(type: Types::STRING)]
    private string $relatedEntity;

    #[Column(type: Types::STRING, enumType: InsightEventType::class)]
    private InsightEventType $eventType;

    #[Column(type: Types::STRING, enumType: InsightContext::class)]
    private InsightContext $context;

    #[Column(type: Types::STRING)]
    private string $messageKey;

    #[Column(type: Types::JSON)]
    private array $payload;

    #[Column(type: Types::BOOLEAN)]
    private bool $read;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    public function __construct(
        string           $messageKey,
        string           $relatedEntity,
        InsightEventType $eventType,
        InsightContext   $context,
        array            $payload
    )
    {
        $this->id            = Uuid::uuid4();
        $this->messageKey    = $messageKey;
        $this->context       = $context;
        $this->payload       = $payload;
        $this->read          = false;
        $this->relatedEntity = $relatedEntity;
        $this->eventType     = $eventType;
        $this->createdAt     = new DateTimeImmutable();
    }

    public function markAsRead(): void
    {
        $this->read = true;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    public function getContext(): InsightContext
    {
        return $this->context;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    public function getRelatedEntity(): string
    {
        return $this->relatedEntity;
    }

    public function getEventType(): InsightEventType
    {
        return $this->eventType;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'            => (string)$this->id,
            'messageKey'    => $this->messageKey,
            'context'       => $this->context->value,
            'payload'       => $this->payload,
            'isRead'        => $this->read,
            'createdAt'     => $this->createdAt->format(DateTimeInterface::RFC3339)
        ];
    }
}
