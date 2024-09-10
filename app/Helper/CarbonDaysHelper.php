<?php

namespace App\Helper;

use Carbon\CarbonInterface;

class CarbonDaysHelper
{
    private static array $allDays = [
        "Sunday" => CarbonInterface::SUNDAY,
        "Monday" => CarbonInterface::MONDAY,
        "Tuesday" => CarbonInterface::TUESDAY,
        "Wednesday" => CarbonInterface::WEDNESDAY,
        "Thursday" => CarbonInterface::THURSDAY,
        "Friday" => CarbonInterface::FRIDAY,
        "Saturday" => CarbonInterface::SATURDAY,
    ];

    public static function getWeekEndDaysCarbon(array $days): array
    {
        return
            array_values(
                array_diff(self::$allDays, array_intersect_key(
                    self::$allDays, array_intersect_key(
                        self::$allDays, array_flip($days)
                    )
                ))
            );
    }
}
