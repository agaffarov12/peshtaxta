<?php
declare(strict_types=1);

namespace App\Utils;

class Pagination
{
    public static function calculateOffset(int $page, int $pageSize): int
    {
        return ($page - 1) * $pageSize;
    }
}
