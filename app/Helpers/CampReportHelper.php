<?php

namespace App\Helpers;

class CampReportHelper
{
    public static function itemStatuses(): array
    {
        return [
            'fully_damaged_replace' => 'dashboard.item_status_fully_damaged_replace',
            'partially_damaged' => 'dashboard.item_status_partially_damaged',
            'maintenance_repair' => 'dashboard.item_status_maintenance_repair',
            'other' => 'dashboard.item_status_other',
        ];
    }

    public static function itemStatusKeys(): array
    {
        return array_keys(self::itemStatuses());
    }
}