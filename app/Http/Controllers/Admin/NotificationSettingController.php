<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Notifications\NotificationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'sms_enabled' => Setting::getValue('sms_enabled', false),
            'sms_twilio_sid' => Setting::getValue('sms_twilio_sid', ''),
            'sms_twilio_token' => Setting::getValue('sms_twilio_token', ''),
            'sms_twilio_from' => Setting::getValue('sms_twilio_from', ''),

            'whatsapp_enabled' => Setting::getValue('whatsapp_enabled', false),
            'whatsapp_twilio_sid' => Setting::getValue('whatsapp_twilio_sid', ''),
            'whatsapp_twilio_token' => Setting::getValue('whatsapp_twilio_token', ''),
            'whatsapp_twilio_from' => Setting::getValue('whatsapp_twilio_from', ''),

            'email_notifications_enabled' => Setting::getValue('email_notifications_enabled', true),
        ];

        return view('admin.settings.notifications', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sms_enabled' => 'boolean',
            'sms_twilio_sid' => 'nullable|string|max:255',
            'sms_twilio_token' => 'nullable|string|max:255',
            'sms_twilio_from' => 'nullable|string|max:30',

            'whatsapp_enabled' => 'boolean',
            'whatsapp_twilio_sid' => 'nullable|string|max:255',
            'whatsapp_twilio_token' => 'nullable|string|max:255',
            'whatsapp_twilio_from' => 'nullable|string|max:50',

            'email_notifications_enabled' => 'boolean',
        ]);

        $validated['sms_enabled'] = $request->boolean('sms_enabled');
        $validated['whatsapp_enabled'] = $request->boolean('whatsapp_enabled');
        $validated['email_notifications_enabled'] = $request->boolean('email_notifications_enabled');

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value, 'notifications');
        }

        return back()->with('success', __('Notification settings updated successfully.'));
    }

    public function test(Request $request, NotificationManager $manager): RedirectResponse
    {
        $request->validate([
            'channel' => 'required|in:sms,whatsapp,email',
            'test_to' => 'required|string|max:255',
        ]);

        $result = $manager->send(
            $request->channel,
            $request->test_to,
            __('Abaan Test Notification'),
            __('This is a test message from Abaan to confirm your :channel configuration is working.', ['channel' => ucfirst($request->channel)])
        );

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? __('Test message sent successfully!')
                : __('Test failed: :error', ['error' => $result['error']])
        );
    }
}