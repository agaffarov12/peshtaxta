<?php
declare(strict_types=1);

namespace Product;

use Doctrine\Common\Collections\Collection;

interface BookingStrategy
{
    public function book(Collection $bookings, Booking $booking): ?Booking;
}
