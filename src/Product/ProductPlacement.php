<?php
declare(strict_types=1);

namespace Product;

use App\Doctrine\ProductIdType;
use App\Doctrine\ProductPlacementIdType;
use Common\Entity\File;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use JsonSerializable;
use Money\Money;

#[Entity]
#[Index(fields: ['price.amount'], name: "price_idx")]
#[Index(fields: ['productId'], name: "placement_product_id")]
class ProductPlacement implements JsonSerializable
{
    #[Id]
    #[Column(type: ProductPlacementIdType::NAME)]
    private ProductPlacementId $id;

    #[Column(type: ProductIdType::NAME)]
    private ProductId $productId;

    #[Column(type: Types::STRING)]
    private string $name;

    #[Column(type: Types::STRING, enumType: PlacementStatus::class)]
    private PlacementStatus $status;

    #[JoinTable(name: 'placement_images')]
    #[JoinColumn(name: 'placement_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'file_id', referencedColumnName: 'id', unique: true)]
    #[ManyToMany(targetEntity: File::class, cascade: ['persist', 'remove'])]
    private Collection $images;

    #[Embedded(class: Money::class)]
    private Money $price;

    public function __construct(?ProductPlacementId $id, string $name, Money $price, ProductId $productId, array $images)
    {
        $this->id        = $id ?? ProductPlacementId::generate();
        $this->productId = $productId;
        $this->name      = $name;
        $this->status    = PlacementStatus::VACANT;
        $this->price     = $price;
        $this->images    = new ArrayCollection($images);
    }

    public function getId(): ProductPlacementId
    {
        return $this->id;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): PlacementStatus
    {
        return $this->status;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(File $image): void
    {
        $this->images->add($image);
    }

    public function setImages(array $images): void
    {
        $this->images = new ArrayCollection($images);
    }

    public function removeImage(int $key): void
    {
        $this->images->remove($key);
    }

    public function setStatus(PlacementStatus $status): void
    {
        $this->status = $status;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'        => (string)$this->id,
            'name'      => $this->name,
            'status'    => $this->status->value,
            'productId' => (string)$this->productId,
            'price'     => $this->price->jsonSerialize(),
            'images'    => $this->images->toArray(),
        ];
    }
}
