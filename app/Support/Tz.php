<?php

namespace App\Support;

use Carbon\Carbon;

class Tz
{
    // Association operates in Malaysia; all payment/receipt times shown in this zone.
    const MY = 'Asia/Kuala_Lumpur';

    /**
     * Format a date/time value in Malaysia time. Accepts Carbon, string or null.
     */
    public static function myt($dt, $format = 'd/m/Y H:i')
    {
        if (empty($dt)) {
            return '';
        }
        return Carbon::parse($dt)->setTimezone(self::MY)->format($format);
    }
}
