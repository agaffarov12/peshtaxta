<?php
declare(strict_types=1);

class CannotExtendCloseCampaignException extends Exception
{
    protected $message = "cannot extend closed campaign";
}
