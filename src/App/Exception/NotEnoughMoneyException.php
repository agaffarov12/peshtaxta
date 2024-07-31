<?php
declare(strict_types=1);

namespace App\Exception;

class NotEnoughMoneyException extends \Exception
{
    protected $message = "account does not have enough money";
}
