<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class CompanyDetails
{
    #[Column(type: Types::STRING, nullable: false)]
    private string $name;

    #[Column(type: Types::STRING, nullable: false)]
    private string $address;

    #[Column(type: Types::STRING, nullable: false)]
    private string $mainBank;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $mfo;

    #[Column(type: Types::STRING, nullable: false)]
    private string $mainXr;

    #[Column(type: Types::BIGINT, nullable: false)]
    private int $inn;

    #[Column(type: Types::STRING, nullable: true)]
    private string $okonx;

    #[Column(type: Types::STRING, nullable: true)]
    private string $additionalBank;

    #[Column(type: Types::STRING, nullable: true)]
    private string $additionalMfo;

    #[Column(type: Types::STRING, nullable: true)]
    private string $additionalXr;
}
