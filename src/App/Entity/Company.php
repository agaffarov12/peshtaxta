<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class Company extends Client
{
    #[Column(type: Types::STRING)]
    private string $name;

    #[Column(type: Types::STRING)]
    private string $address;

    #[Column(type: Types::STRING)]
    private string $mainBank;

    #[Column(type: Types::INTEGER)]
    private int $mfo;

    #[Column(type: Types::STRING)]
    private string $mainXr;

    #[Column(type: Types::BIGINT)]
    private int $inn;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $okonx;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $additionalBank;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $additionalMfo;

    #[Column(type: Types::STRING, nullable: true)]
    private ?string $additionalXr;

    public function __construct(
        string         $firstName,
        string         $lastName,
        ContactDetails $contactDetails,
        ClientType     $type,
        ClientCategory $category,
        ClientOrigin   $origin,
        array          $tags,
        array          $files,
        string         $name,
        string         $address,
        string         $mainBank,
        int            $mfo,
        string         $mainXr,
        int            $inn,
        string         $okonx = null,
        string         $additionalBank = null,
        string         $additionalMfo = null,
        string         $additionalXr = null,
        string         $surname = null,
        string         $comment = null
    )
    {
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

        $this->name           = $name;
        $this->address        = $address;
        $this->mainBank       = $mainBank;
        $this->mfo            = $mfo;
        $this->mainXr         = $mainXr;
        $this->inn            = $inn;
        $this->okonx          = $okonx;
        $this->additionalBank = $additionalBank;
        $this->additionalMfo  = $additionalMfo;
        $this->additionalXr   = $additionalXr;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function setMainBank(string $mainBank): void
    {
        $this->mainBank = $mainBank;
    }

    public function setMfo(int $mfo): void
    {
        $this->mfo = $mfo;
    }

    public function setMainXr(string $mainXr): void
    {
        $this->mainXr = $mainXr;
    }

    public function setInn(int $inn): void
    {
        $this->inn = $inn;
    }

    public function setOkonx(string $okonx): void
    {
        $this->okonx = $okonx;
    }

    public function setAdditionalBank(string $additionalBank): void
    {
        $this->additionalBank = $additionalBank;
    }

    public function setAdditionalMfo(string $additionalMfo): void
    {
        $this->additionalMfo = $additionalMfo;
    }

    public function setAdditionalXr(string $additionalXr): void
    {   
        $this->additionalXr = $additionalXr;
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
            'files' => $this->files->toArray(),
            'company' => [
                'name' => $this->name,
                'address' => $this->address,
                'mainBank' => $this->mainBank,
                'mfo' => $this->mfo,
                'mainXr' => $this->mainXr,
                'inn'   => $this->inn,
                'okonx' => $this->okonx,
                'additionalBank' => $this->additionalBank,
                'additionalMfo' => $this->additionalMfo,
                'additionalXr' => $this->additionalXr,
            ],
        ];
    }
}
