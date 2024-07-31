<?php
declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use JsonSerializable;

#[Entity]
class DirectAdvertiser extends Client implements JsonSerializable
{
    #[Column(type: Types::STRING, nullable: true)]
    private ?string $passportSeriesAndNumber;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $dateOfPassportIssue;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $passportAuthority;

    #[Column(type: Types::BIGINT, nullable: true)]
    private ?int $passportInn;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $dateOfBirth;

    public function __construct(
        string            $firstName,
        string            $lastName,
        ContactDetails    $contactDetails,
        ClientType        $type,
        ClientCategory    $category,
        ClientOrigin      $origin,
        array             $tags,
        array             $files,
        ?string            $passportSeriesAndNumber,
        ?string            $passportAuthority,
        ?int               $passportInn,
        ?DateTimeImmutable $dateOfPassportIssue,
        ?DateTimeImmutable $dateOfBirth,
        ?string            $surname = null,
        ?string            $comment = null
    ) {
        parent::__construct(
            $firstName,
            $lastName,
            $contactDetails,
            $type,
            $category,
            $origin,
            $tags,
            $files,
            $surname,
            $comment
        );

        $this->passportSeriesAndNumber = $passportSeriesAndNumber;
        $this->passportAuthority       = $passportAuthority;
        $this->passportInn             = $passportInn;
        $this->dateOfPassportIssue     = $dateOfPassportIssue;
        $this->dateOfBirth             = $dateOfBirth;
    }

    public function getPassportSeriesAndNumber(): string
    {
        return $this->passportSeriesAndNumber;
    }

    public function getDateOfPassportIssue(): DateTimeImmutable
    {
        return $this->dateOfPassportIssue;
    }

    public function getPassportAuthority(): string
    {
        return $this->passportAuthority;
    }

    public function getPassportInn(): int
    {
        return $this->passportInn;
    }

    public function getDateOfBirth(): DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function setPassportSeriesAndNumber(string $passportSeriesAndNumber): void
    {
        $this->passportSeriesAndNumber = $passportSeriesAndNumber;
    }

    public function setDateOfPassportIssue(DateTimeImmutable $dateOfPassportIssue): void
    {
        $this->dateOfPassportIssue = $dateOfPassportIssue;
    }

    public function setPassportAuthority(string $passportAuthority): void
    {
        $this->passportAuthority = $passportAuthority;
    }

    public function setPassportInn(int $passportInn): void
    {
        $this->passportInn = $passportInn;
    }

    public function setDateOfBirth(DateTimeImmutable $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'        => (string) $this->id,
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'surname'   => $this->surname,
            'comment'   => $this->comment,
            'category'  => $this->category,
            'origin'    => $this->origin,
            'type'      => $this->type->value,
            'contactDetails' => array_merge(['origin' => $this->origin->jsonSerialize()], $this->contactDetails->jsonSerialize()),
            'tags'          => $this->tags->toArray(),
            'files'         => $this->files->toArray(),
            'passport' => [
                'seriesAndNumber' => $this->passportSeriesAndNumber,
                'authority' => $this->passportAuthority,
                'inn' => $this->passportInn,
                'dateOfIssue' => $this->dateOfPassportIssue?->format(DateTimeInterface::RFC3339),
                'dateOfBirth' => $this->dateOfBirth?->format(DateTimeInterface::RFC3339)
            ],
        ];
    }
}
