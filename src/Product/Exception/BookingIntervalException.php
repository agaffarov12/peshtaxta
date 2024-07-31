<?php
declare(strict_types=1);

namespace Product\Exception;

use Exception;

class BookingIntervalException extends Exception
{
    protected $message = "Booking period must equal or exceed 2 days ";
}
