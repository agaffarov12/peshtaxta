<?php
declare(strict_types=1);

namespace Campaign;

use App\Doctrine\CreativeIdType;
use App\Doctrine\ProductPlacementIdType;
use Common\Entity\File;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use JsonSerializable;
use Product\ProductPlacementId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Creative implements JsonSerializable
{
    #[Id]
    #[Column(type: CreativeIdType::NAME)]
    private CreativeId $id;

    #[Column(type: ProductPlacementIdType::NAME)]
    private ProductPlacementId $productPlacementId;

    #[OneToOne(targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(name: 'file_id', referencedColumnName: 'id')]
    private File $file;

    #[Column(type: Types::STRING)]
    private bool $mounted;

    public function __construct(ProductPlacementId $productPlacementId, File $file, bool $mounted)
    {
        $this->id                 = CreativeId::generate();
        $this->productPlacementId = $productPlacementId;
        $this->file               = $file;
        $this->mounted            = $mounted;
    }

    public function mount(): void
    {
        $this->mounted = true;
    }

    public function unmount(): void
    {
        $this->mounted = false;
    }

    public function getId(): CreativeId
    {
        return $this->id;
    }

    public function getProductPlacementId(): ProductPlacementId
    {
        return $this->productPlacementId;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function isMounted(): bool
    {
        return $this->mounted;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => (string) $this->id,
            'placementId' => (string) $this->productPlacementId,
            'file' => $this->file->jsonSerialize(),
            'mounted' => $this->mounted
        ];
    }
}
