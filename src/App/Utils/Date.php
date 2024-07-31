<?php
declare(strict_types=1);

namespace App\Utils;

use DateTimeImmutable;

class Date
{
    public static function getDateDifferenceInDays(DateTimeImmutable $dateA, DateTimeImmutable $dateB): int
    {
        return $dateA->diff($dateB)->days;
    }
}
