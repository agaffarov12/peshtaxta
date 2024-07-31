<?php
declare(strict_types=1);

namespace Product\Exception;

use Exception;

class BookingIntervalTooShortException extends Exception
{
    protected $message = "Extended booking interval too short";
}
