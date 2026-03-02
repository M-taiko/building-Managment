<?php

use App\Helpers\CurrencyHelper;

if (!function_exists('currency')) {
    /**
     * Format amount with currency
     */
    function currency($amount, $decimals = 2): string
    {
        return CurrencyHelper::format($amount, $decimals);
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol
     */
    function currency_symbol(): string
    {
        return CurrencyHelper::symbol();
    }
}
