<?php

declare(strict_types=1);

namespace App\Controller\Utils\Visit;

class Calculate
{
    public static function duration(int $forms, int $products): int
    {
        return ($forms * 15) + ($products * 5);
    }
}
