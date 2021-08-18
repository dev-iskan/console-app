<?php

namespace App\Helpers;

class Validation
{
    public static function title($title): bool
    {
        return strlen($title) >= 3 && strlen($title) <= 14;
    }

    public static function eId($eId): bool
    {
        return is_numeric($eId);
    }

    public static function price($price): bool
    {
        return is_float($price) && $price >= 0 && $price <= 200;
    }

    public static function in_array_all($needles, $haystack): bool
    {
        return empty(array_diff($needles, $haystack));
    }
}
