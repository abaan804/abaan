<?php

namespace Modules\Masjid\Services;

use App\Models\Setting;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSetting;

class MasjidSettingService
{
    protected array $cache = [];

    /**
     * Get the MasjidSetting for a mosque, or create with defaults inherited from Abaan global settings.
     */
    public function forMosque(MasjidMosque $mosque): MasjidSetting
    {
        if (isset($this->cache[$mosque->id])) {
            return $this->cache[$mosque->id];
        }

        $setting = MasjidSetting::firstOrCreate(
            ['mosque_id' => $mosque->id],
            [
                'company_id' => $mosque->company_id,
                'currency_symbol' => Setting::getValue('currency_symbol', 'Rs'),
                'currency_code' => Setting::getValue('currency_code', 'PKR'),
                'currency_position' => Setting::getValue('currency_position', 'before'),
                'receipt_prefix' => 'MCM-',
                'default_reminder_days' => 3,
                'notification_whatsapp' => false,
                'notification_sms' => false,
                'notification_email' => false,
                'default_language' => 'en',
            ]
        );

        $this->cache[$mosque->id] = $setting;

        return $setting;
    }

    /**
     * Format a currency amount using mosque-specific settings.
     */
    public function formatCurrency(MasjidMosque $mosque, float $amount): string
    {
        $setting = $this->forMosque($mosque);
        $formatted = number_format($amount, 2);

        return $setting->currency_position === 'after'
            ? "{$formatted} {$setting->currency_symbol}"
            : "{$setting->currency_symbol}{$formatted}";
    }

    /**
     * Generate the next sequential receipt number for a mosque.
     * Format: {prefix}{year}-{zero-padded sequence}
     * e.g. MCM-2026-00047
     */
    public function nextReceiptNumber(MasjidMosque $mosque): string
    {
        $setting = $this->forMosque($mosque);
        $prefix = $setting->receipt_prefix . now()->year . '-';

        $last = \Modules\Masjid\Models\MasjidPayment::where('mosque_id', $mosque->id)
            ->where('receipt_no', 'like', $prefix . '%')
            ->orderByDesc('receipt_no')
            ->value('receipt_no');

        $lastNumber = $last ? (int) substr($last, strlen($prefix)) : 0;

        return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }
}