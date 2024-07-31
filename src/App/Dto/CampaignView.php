<?php
declare(strict_types=1);

namespace App\Dto;

use App\Entity\ClientType;
use Campaign\CampaignStatus;
use Campaign\Creative;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Product\BookingStatus;

class CampaignView implements JsonSerializable
{
    public function __construct(
        public readonly string            $id,
        public readonly string            $price,
        public readonly CampaignStatus    $status,
        public readonly string            $bookingId,
        public readonly BookingStatus     $bookingStatus,
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate,
        public readonly string            $placementId,
        public readonly string            $placementName,
        public readonly int               $placementPrice,
        public readonly string            $clientId,
        public readonly string            $clientFirstName,
        public readonly string            $clientLastName,
        public readonly ClientType        $clientType,
        public readonly string            $productId,
        public readonly string            $productName,
        public readonly ?string           $orderId = null,
        public readonly ?string           $creativeFileName = null,
        public readonly ?string           $creativeFileId = null,
        public readonly ?bool             $creativeMounted = null,
        
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id'               => $this->id,
            'price'            => $this->price,
            'status'           => $this->status->value,
            'bookingId'        => $this->bookingId,
            'bookingStatus'    => $this->bookingStatus->value,
            'startDate'        => $this->startDate->format(DateTimeInterface::RFC3339),
            'endDate'          => $this->endDate->format(DateTimeInterface::RFC3339),
            'placementId'      => $this->placementId,
            'placementName'    => $this->placementName,
            'placementPrice'   => $this->placementPrice,
            'clientId'         => $this->clientId,
            'clientFirstName'  => $this->clientFirstName,
            'clientLastName'   => $this->clientLastName,
            'clientType'       => $this->clientType->value,
            'productId'        => $this->productId,
            'productName'      => $this->productName,
	        'orderId'          => $this->orderId,
            'creativeFileName' => $this->creativeFileName,
            'creativeFileId'   => $this->creativeFileId,
            'creativeMounted'  => $this->creativeMounted,
            
        ];
    }
}
