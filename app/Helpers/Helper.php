<?php

namespace App\Helpers;

use Carbon\Carbon;

class Helper
{
    public static function dateIndo($date) {
        $convertDate = Carbon::parse($date)->translatedFormat('d F Y');
        return $convertDate;
    }
}
