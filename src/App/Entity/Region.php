<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use JsonSerializable;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping\Column;
use Ramsey\Uuid\Doctrine\UuidType;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Ramsey\Uuid\Uuid;

#[Entity]
class Region implements JsonSerializable
{
    #[Id]
    #[Column(type: UuidType::NAME)]
    private UuidInterface $id;

    #[Column(type: Types::STRING)]    
    private string $name;

    #[Column(type: Types::BOOLEAN)]
    private bool $enabled;

    #[ManyToOne(targetEntity: Region::class, inversedBy: 'children')]
    #[JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
    private ?Region $parent;

    #[OneToMany(mappedBy: 'parent', targetEntity: Region::class, cascade: ['persist', 'remove'])]
    private Collection $children;

    public function __construct(string $name)
    {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->enabled = true;
        $this->children = new ArrayCollection();
    }

    public function setParent(Region $parent): void
    {
        $this->parent = $parent;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getChildren(): array
    {
        return $this->children->toArray();
    }

    public function addChildRegion(Region $region): void
    {
        $this->children->add($region);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'   => (string) $this->id,
            'name' => $this->name,
            'parent'   => $this->parent ? (string) $this->parent->getId() : null,
            'children' => $this->getChildren(),
            'enabled' => $this->enabled,
        ];
    }
}
