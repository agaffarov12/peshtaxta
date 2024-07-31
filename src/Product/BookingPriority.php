<?php
declare(strict_types=1);

namespace Product;

enum BookingPriority: string
{
    case LOW = "low";
    case MEDIUM = "medium";
    case HIGH = "high";
}
