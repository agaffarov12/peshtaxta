<?php
declare(strict_types=1);

namespace Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ChangeTrackingPolicy;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use JsonSerializable;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity]
#[ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class ProductCategory implements JsonSerializable
{
    #[Id]
    #[Column(type: UuidType::NAME)]
    private UuidInterface $id;

    #[Column(type: Types::STRING)]
    private string $name;

    #[Column(type: Types::BOOLEAN)]
    private bool $disabled;

    #[OneToMany(mappedBy: 'parent', targetEntity: ProductCategory::class, cascade: ['persist', 'remove'])]
    private Collection $children;

    #[ManyToOne(targetEntity: ProductCategory::class, inversedBy: 'children')]
    #[JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
    private ?ProductCategory $parent;

    public function __construct(string $name)
    {
        $this->id       = Uuid::uuid4();
        $this->name     = $name;
        $this->children = new ArrayCollection();
        $this->disabled = false;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addChild(ProductCategory $child): void
    {
        $this->children->add($child);
    }

    public function getChildren(): array
    {
        return $this->children->toArray();
    }

    public function getParent(): ProductCategory
    {
        return $this->parent;
    }

    public function disable(): void
    {
        $this->disabled = true;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setParent(?ProductCategory $parent): void
    {
        $this->parent = $parent;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'       => (string)$this->id,
            'name'     => $this->name,
            'parent'   => $this->parent ? (string) $this->parent->getId() : null,
            'children' => $this->getChildren(),
            'disabled' => $this->disabled,
        ];
    }
}