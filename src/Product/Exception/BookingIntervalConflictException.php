<?php
declare(strict_types=1);

namespace Product\Exception;

use Exception;

class BookingIntervalConflictException extends Exception
{
    protected $message = "Given booking interval intersects with other";
}
