<?php
declare(strict_types=1);

namespace Product;

enum PlacementStatus: string
{
    case VACANT = "vacant";
    case OCCUPIED = "occupied";
}
