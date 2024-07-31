<?php
declare(strict_types=1);

namespace App\Utils;

use DateTimeImmutable;
use DateTimeInterface;

class DateTimeFormatConverter
{
    const COMMON_STRING_FORMAT = DATE_RFC3339_EXTENDED;

    public static function fromDateTime(DateTimeInterface $dateTime): string
    {
        return $dateTime->format(self::COMMON_STRING_FORMAT);
    }

    public static function toDateTime(string $commonFormatDateTime): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat(self::COMMON_STRING_FORMAT, $commonFormatDateTime);
    }
}
