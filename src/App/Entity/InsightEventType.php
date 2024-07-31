<?php
declare(strict_types=1);

namespace App\Entity;

enum InsightEventType: string
{
    case UNPAID_CAMPAIGN_START = "unpaid_campaign_start";
    case CAMPAIGN_START = "campaign_start";
    case CAMPAIGN_END = "campaign_end";
}
