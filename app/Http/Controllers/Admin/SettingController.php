<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'site_name' => setting('site_name', 'Abaan'),
            'default_locale' => setting('default_locale', 'en'),
            'email_verification_enabled' => setting('email_verification_enabled', false),
            'currency_symbol' => setting('currency_symbol', '$'),
            'currency_code' => setting('currency_code', 'USD'),
            'currency_position' => setting('currency_position', 'before'),
            'date_format' => setting('date_format', 'M d, Y'),
        ];

        $dateFormatOptions = [
            'M d, Y' => now()->translatedFormat('M d, Y'),
            'd/m/Y' => now()->translatedFormat('d/m/Y'),
            'm/d/Y' => now()->translatedFormat('m/d/Y'),
            'Y-m-d' => now()->translatedFormat('Y-m-d'),
            'd-m-Y' => now()->translatedFormat('d-m-Y'),
            'd F Y' => now()->translatedFormat('d F Y'),
        ];

        return view('admin.settings.index', compact('settings', 'dateFormatOptions'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'default_locale' => 'required|in:en,ur,ar',
            'email_verification_enabled' => 'boolean',
            'currency_symbol' => 'required|string|max:10',
            'currency_code' => 'required|string|max:3',
            'currency_position' => 'required|in:before,after',
            'date_format' => 'required|string|max:20',
        ]);

        $validated['email_verification_enabled'] = $request->boolean('email_verification_enabled');
       
        // Add to your admin settings update logic:
        $keys = [
            'bank_name', 'account_title', 'account_number',
            'iban', 'jazzcash_number', 'easypaisa_number',
        ];

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value, 'general');
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}