<?php

namespace Packages\Report\Config;

class ReportConstants
{
    const LAST_MONTH = 'last_month';
    const THIS_MONTH = 'this_month';
    const LAST_7_DAYS = 'last_7_days';
    const LAST_90_DAYS = 'last_90_days';

    const CUSTOM = 'custom';
    const YEAR = 'year';
    const TODAY = 'today';
    const LOW_IN_STOCK = 'low_in_stock';
    const OUT_OF_STOCK = 'out_of_stock';
    const MOST_STOCKED = 'most_stocked';

    public static function allFilters()
    {
        return [
            self::LAST_MONTH,
            self::THIS_MONTH,
            self::LAST_7_DAYS,
            self::LAST_90_DAYS,
            self::CUSTOM,
            self::YEAR,
            self::TODAY
        ];
    }
    public static function stockFilters()
    {
        return [
            self::LOW_IN_STOCK,
            self::OUT_OF_STOCK,
            self::MOST_STOCKED,
        ];
    }
}