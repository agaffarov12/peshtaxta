<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use JsonSerializable;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity]
class TransactionCategory implements JsonSerializable
{
    #[Id]
    #[Column(type: UuidType::NAME)]
    private UuidInterface $id;

    #[Column(type: Types::STRING)]
    private string $name;

    #[OneToMany(mappedBy: 'parent', targetEntity: TransactionCategory::class, cascade: ['persist', 'remove'])]
    private Collection $children;

    #[ManyToOne(targetEntity: TransactionCategory::class, inversedBy: 'children')]
    #[JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
    private ?TransactionCategory $parent;

    #[Column(type: Types::BOOLEAN)]
    private bool $disabled;

    public function __construct(string $name)
    {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->disabled = false;
        $this->children = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function disable(): void
    {
        $this->disabled = true;
    }

    public function setParent(?TransactionCategory $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): array
    {
        return $this->children->toArray();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function removeParent(): void
    {
        $this->parent = null;
    }

    public function addChild(TransactionCategory $child): void
    {
        $this->children->add($child);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'disabled' => $this->disabled,
            'children' => $this->getChildren(),
            'parent' => $this->parent ? (string) $this->parent->getId() : null,
        ];
    }
}
