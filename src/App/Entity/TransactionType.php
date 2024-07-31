<?php
declare(strict_types=1);

namespace App\Entity;

enum TransactionType: string
{
    case INCOME = "income";
    case EXPENDITURE = "expenditure";
}
