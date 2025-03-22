<?php

namespace gst\helper;

class gsthelper
{
    public static function financial_year_of($date)
    {
        return date('Y', strtotime('+9 month', strtotime($date)));
    }

    public static function gst_period_start($date)
    {
        $m = sprintf('%02d', (floor(substr($date, 5, 2) / 2) * 2 + 11) % 12 + 1);
        $y = date('Y', strtotime($date)) - ($m > date('m', strtotime($date)) ? 1 : 0);

        return "$y-$m-01";
    }

    public static function gst_period_end($date)
    {
        $start = static::gst_period_start($date);

        return date('Y-m-d', strtotime('+2 month -1 day', strtotime($start)));
    }

    public static function gst_period_id($date)
    {
        $start = static::gst_period_start($date);
        $y = static::financial_year_of($start);
        $m = substr($start, 5, 2);

        $n = match($m) {
            '02' => '6',
            '04' => '1',
            '06' => '2',
            '08' => '3',
            '10' => '4',
            '12' => '5',
        };

        return "$y-$n";
    }
}
