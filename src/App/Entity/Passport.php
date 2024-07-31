<?php
declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class Passport
{
    #[Column(type: Types::STRING)]
    private string $series;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $dateOfIssue;

    #[Column(type: Types::STRING)]
    private string $authority;

    #[Column(type: Types::BIGINT)]
    private int $inn;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $dateOfBirth;
}
