<?php
declare(strict_types=1);

namespace App\Entity;

enum StatisticsInterval: string
{
    case DAILY = "daily";
    case WEEKLY = "weekly";
    case MONTHLY = "monthly";
}