<?php

use App\Models\Setting;
use Illuminate\Support\Carbon;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::getValue($key, $default);
    }
}

if (! function_exists('formatCurrency')) {
    function formatCurrency(float|int|null $amount, ?string $currencyCode = null): string
    {
        $amount = $amount ?? 0;
        $symbol = setting('currency_symbol', '$');
        $position = setting('currency_position', 'before'); // before | after
        $formatted = number_format($amount, 2);

        return $position === 'after'
            ? "{$formatted} {$symbol}"
            : "{$symbol}{$formatted}";
    }
}

if (! function_exists('formatDate')) {
    function formatDate(mixed $date, ?string $format = null): string
    {
        if (empty($date)) {
            return '—';
        }

        if (! $date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $format = $format ?? setting('date_format', 'M d, Y');

        return $date->translatedFormat($format);
    }
}

if (! function_exists('formatDateTime')) {
    function formatDateTime(mixed $date): string
    {
        if (empty($date)) {
            return '—';
        }

        if (! $date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $dateFormat = setting('date_format', 'M d, Y');

        return $date->translatedFormat("{$dateFormat} h:i A");
    }
}