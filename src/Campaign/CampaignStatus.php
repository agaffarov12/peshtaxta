<?php
declare(strict_types=1);

namespace Campaign;

enum CampaignStatus: string
{
    case CREATED = "created";
    case ACTIVE = "active";
    case CLOSED = "closed";
    case CANCELLED = "cancelled";
    case BOOKING_CANCELLED = "booking_cancelled";
    case RESERVED = "reserved";
}
