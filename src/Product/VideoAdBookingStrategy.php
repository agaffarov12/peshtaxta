<?php
declare(strict_types=1);

namespace Product;

use Doctrine\Common\Collections\Collection;
use Product\Exception\BookingIntervalException;

class VideoAdBookingStrategy implements BookingStrategy
{
    /**
     * @throws BookingIntervalException
     */
    public function book(Collection $bookings, Booking $booking): ?Booking
    {
        if ($this->intervalIsLessThanTwoDays($booking))  {
            throw new BookingIntervalException();
        }

        return $booking;
    }

    private function intervalIsLessThanTwoDays(Booking $booking): bool
    {
        $interval = $booking->getStartDate()->diff($booking->getEndDate());

        return $interval->days < 2;
    }
}
