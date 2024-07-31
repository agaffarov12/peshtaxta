<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use JsonSerializable;

#[Embeddable]
class ContactDetails implements JsonSerializable
{
    #[Column(type: Types::STRING)]
    private string $phoneNumber;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $phoneNumber2;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $phoneNumber3;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $email;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $telegram;

    public function __construct(
        string $phoneNumber,
        string $phoneNumber2 = null,
        string $phoneNumber3 = null,
        string $email = null,
        string $telegram = null
    )
    {
        $this->phoneNumber  = $phoneNumber;
        $this->phoneNumber2 = $phoneNumber2;
        $this->phoneNumber3 = $phoneNumber3;
        $this->email        = $email;
        $this->telegram     = $telegram;
    }

    private function listPhoneNumbers(): array
    {
        $numbers = [$this->phoneNumber];

        if($this->phoneNumber2 !== null) {
            $numbers[] = $this->phoneNumber;
        }

        if ($this->phoneNumber3 !== null) {
            $numbers[] = $this->phoneNumber;
        }

        return $numbers;
    }

    public function jsonSerialize(): array
    {
        return [
            'phoneNumbers' => $this->listPhoneNumbers(),
            'email'        => $this->email,
            'telegram'     => $this->telegram,
        ];
    }
}
