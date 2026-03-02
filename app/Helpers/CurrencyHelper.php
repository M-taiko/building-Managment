<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Get currency symbol
     */
    public static function symbol(): string
    {
        return 'ج.م';
    }

    /**
     * Get currency name
     */
    public static function name(): string
    {
        return 'جنيه مصري';
    }

    /**
     * Format amount with currency
     */
    public static function format($amount, $decimals = 2): string
    {
        return number_format($amount, $decimals) . ' ' . self::symbol();
    }
}
