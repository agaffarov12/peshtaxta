<?php
declare(strict_types=1);

namespace App\Utils;

use App\Entity\StatisticsInterval;
use DateInterval;

class DateIntervalUtil 
{
    public static function getIntervalObject(StatisticsInterval $interval): DateInterval
    {
        $dateInterval = new DateInterval('P1D');

        switch($interval) {
            case StatisticsInterval::WEEKLY:
                $dateInterval = new DateInterval('P7D');
                break;
            case StatisticsInterval::MONTHLY:
                $dateInterval = new DateInterval('P1M');    
                break;
            default:
                $dateInterval = new DateInterval('P1D');   
        }

        return $dateInterval;
    }
}