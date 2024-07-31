<?php
declare(strict_types=1);

namespace App\Entity;

use App\Doctrine\ClientIdType;
use Client\ClientId;
use Common\Entity\Tag;
use Common\Entity\File;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use JsonSerializable;

#[Entity]
#[Table(name: "clients")]
#[InheritanceType('JOINED')]
#[DiscriminatorColumn(name: 'discr', type: 'string')]
#[DiscriminatorMap(['client' => Client::class, 'directAdvertiser' => DirectAdvertiser::class, 'company' => Company::class])]
#[Index(fields: ["firstName"], name: "first_name_idx")]
#[Index(fields: ["lastName"], name: "last_name_idx")]
#[Index(fields: ["type"], name: "type_idx")]
#[Index(fields: ["deleted"], name: "deleted_client_idx")]
class Client implements JsonSerializable
{
    #[Id]
    #[Column(type: ClientIdType::NAME)]
    protected ClientId $id;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    protected DateTimeImmutable $createdAt;

    #[Column(type: Types::STRING)]
    protected string $firstName;

    #[Column(type: Types::STRING)]
    protected string $lastName;

    #[Column(type: Types::BOOLEAN)]
    protected bool $deleted;

    #[Column(type: Types::BOOLEAN)]
    protected bool $indebted;

    #[ManyToOne(targetEntity: ClientOrigin::class)]
    protected ClientOrigin $origin;

    #[Column(type: Types::STRING, nullable: true)]
    protected ?string $surname;

    #[Column(type: Types::TEXT, nullable: true)]
    protected ?string $comment;

    #[Embedded(class: ContactDetails::class)]
    protected ContactDetails $contactDetails;

    #[Column(type: Types::STRING, nullable: false, enumType: ClientType::class)]
    protected ClientType $type;

    #[ManyToOne(targetEntity: ClientCategory::class)]
    protected ClientCategory $category;

    #[JoinTable(name: 'client_tags')]
    #[JoinColumn(name: 'client_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    #[ManyToMany(targetEntity: Tag::class, cascade: ["persist"])]
    protected Collection $tags;

    #[JoinTable(name: 'client_files')]
    #[JoinColumn(name: 'client_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'file_id', referencedColumnName: 'id', unique: true)]
    #[ManyToMany(targetEntity: File::class, cascade: ['persist', 'remove'])]
    protected Collection $files;

    public function __construct(
        string         $firstName,
        string         $lastName,
        ContactDetails $contactDetails,
        ClientType     $type,
        ClientCategory $category,
        ClientOrigin   $origin,
        array          $tags = [],
        array          $files = [],
        string         $surname = null,
        string         $comment = null,
    )
    {
        $this->id             = ClientId::generate();
        $this->firstName      = $firstName;
        $this->lastName       = $lastName;
        $this->deleted        = false;
        $this->indebted       = false;
        $this->surname        = $surname;
        $this->comment        = $comment;
        $this->contactDetails = $contactDetails;
        $this->type           = $type;
        $this->category       = $category;
        $this->origin         = $origin;
        $this->createdAt      = (new DateTimeImmutable())->setTime(0,0);
        $this->tags           = new ArrayCollection($tags);
        $this->files          = new ArrayCollection($files);
    }

    public function jsonSerialize(): array
    {
        return [
            'id'        => (string) $this->id,
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'surname'   => $this->surname,
            'comment'   => $this->comment,
            'category'  => $this->category->getName(),
            'type'      => $this->type->value,
            'indebted'  => $this->indebted,
            'contactDetails' => array_merge(['origin' => $this->origin->getName()], $this->contactDetails->jsonSerialize())
        ];
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getOrigin(): ClientOrigin
    {
        return $this->origin;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function isIndebted(): bool
    {
        return $this->indebted;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function addFile(File $file)
    {
        $this->files->add($file);
    }

    public function addTag(Tag $tag)
    {
        $this->tags->add($tag);
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function removeFile(int $key): void
    {
        $this->files->remove($key);
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setIndebted(bool $indebted): void
    {
        $this->indebted = $indebted;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setOrigin(ClientOrigin $origin): void
    {
        $this->origin = $origin;
    }

    public function setSurname(?string $surname): void
    {
        $this->surname = $surname;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function setContactDetails(ContactDetails $contactDetails): void
    {
        $this->contactDetails = $contactDetails;
    }

    public function setType(ClientType $type): void
    {
        $this->type = $type;
    }

    public function setCategory(ClientCategory $category): void
    {
        $this->category = $category;
    }

    public function setTags(array $tags): void
    {
        $this->tags = new ArrayCollection($tags);
    }

    public function setFiles(Collection $files): void
    {
        $this->files = $files;
    }
}
