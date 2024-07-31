<?php
declare(strict_types=1);

namespace Product;

enum BookingStatus: string
{
    case DEFAULT = "default";
    case CANCELLED = "cancelled";
    case EXTENDED = "extended";
}
