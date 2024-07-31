<?php
declare(strict_types=1);

namespace App\Entity;

enum ClientType: string
{
    case COMPANY = "company";
    case INDIVIDUAL = "individual";
}
