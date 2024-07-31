<?php
declare(strict_types=1);

namespace App\Utils;

class Arrays
{
    public static function columns(array $array, array $columns): array
    {
        $keys = array_flip($columns);

        return array_intersect_key($array, $keys);
    }
}
