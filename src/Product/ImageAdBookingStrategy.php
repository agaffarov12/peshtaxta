<?php
declare(strict_types=1);

namespace Product;

use Doctrine\Common\Collections\Collection;
use Product\Exception\BookingIntervalConflictException;
use Product\Exception\BookingIntervalException;

class ImageAdBookingStrategy implements BookingStrategy
{
    /**
     * @throws BookingIntervalConflictException
     * @throws BookingIntervalException
     */
    public function book(Collection $bookings, Booking $booking): ?Booking
    {
        if ($this->intervalIntersectsWithOtherBookings($booking, $bookings)) {
            throw new BookingIntervalConflictException();
        }

        if ($this->intervalIsLessThanTwoDays($booking)) {
            throw new BookingIntervalException();
        }

        return $booking;
    }

    private function intervalIntersectsWithOtherBookings(Booking $booking, Collection $bookings): bool
    {
        if ($booking->getPriority() === BookingPriority::LOW || $booking->getPriority() === BookingPriority::MEDIUM) {
            return $this->checkLowerPriorityBookingsForIntersection($booking, $bookings, BookingPriority::LOW);
        }

        /** @var Booking $b */
        foreach ($bookings as $b) {
            if (
                $booking->getPlacement()->getId() === $b->getPlacement()->getId() &&
                $booking->getStartDate() < $b->getEndDate() &&
                $b->getStartDate() < $booking->getEndDate() &&
                $b->getStatus() !== BookingStatus::CANCELLED &&
                $b->getStatus() !== BookingStatus::EXTENDED
            ) {
                return true;
            }
        }

        return false;
    }

    private function intervalIsLessThanTwoDays(Booking $booking): bool
    {
        $interval = $booking->getStartDate()->diff($booking->getEndDate());

        return $interval->days < 2;
    }

    private function checkLowerPriorityBookingsForIntersection(
        Booking $booking,
        Collection $bookings,
        BookingPriority $priority
    ): bool {
        /** @var Booking $b */
        foreach ($bookings as $b) {
            if (
                $priority === $b->getPriority() &&
                $booking->getPlacement()->getId() === $b->getPlacement()->getId() &&
                $booking->getStartDate() < $b->getEndDate() &&
                $b->getStartDate() < $booking->getEndDate() &&
                $b->getStatus() !== BookingStatus::CANCELLED &&
                $b->getStatus() !== BookingStatus::EXTENDED
            ) {
                return true;
            }
        }

        return false;
    }

    private function checkMediumPriorityBookingsForIntersection(Booking $booking, Collection $bookings): bool
    {

    }
}
