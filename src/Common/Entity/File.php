<?php
declare(strict_types=1);

namespace Common\Entity;

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
#[Table(name: "files")]
class File implements JsonSerializable
{
    #[Id]
    #[Column(type: UuidType::NAME)]
    private UuidInterface $id;

    #[Column(type: Types::STRING)]
    private string $name;

    #[Column(type: Types::STRING)]
    private string $path;

    #[Column(type: Types::JSON)]
    private array $metadata;

    public function __construct(string $path)
    {
        $this->id   = Uuid::uuid4();
        $this->path = $path;
        $this->name = $this->extractFilename($path);
        $this->metadata = $this->prepareMetadata();
    }

    private function extractFilename(string $path): string
    {
        return basename($path);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    private function getPathForClient(): string
    {
        $arrayFromPath = explode('/', $this->path);

        array_shift($arrayFromPath);
        array_shift($arrayFromPath);

        return implode("/", $arrayFromPath);
    }

    private function prepareMetadata(): array
    {
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);
        $metaData = [];

        if ($ext === 'png' || $ext === 'jpg') {
            list($width, $height) = getimagesize($this->path);

            $metaData['width'] = $width;
            $metaData['height'] = $height;
        }

        return $metaData;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'   => (string) $this->id,
            'name' => $this->name,
            'path' => $this->getPathForClient(),
            'metadata' => $this->metadata
        ];
    }
}
