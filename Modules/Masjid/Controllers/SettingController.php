<?php

namespace Modules\Masjid\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Masjid\Models\MasjidMosque;
use Modules\Masjid\Models\MasjidSetting;
use Modules\Masjid\Requests\StoreSettingRequest;
use Modules\Masjid\Services\MasjidSettingService;

class SettingController extends Controller
{
    public function __construct(protected MasjidSettingService $settingService)
    {
    }

    public function edit(Request $request, MasjidMosque $mosque): View
    {
        abort_unless($request->user()->can('masjid.manage-settings')
            && $mosque->company_id === $request->user()->company_id, 403);

        $setting = $this->settingService->forMosque($mosque);

        return view('masjid::settings.edit', compact('mosque', 'setting'));
    }

    public function update(StoreSettingRequest $request, MasjidMosque $mosque): RedirectResponse
    {
        $setting = $this->settingService->forMosque($mosque);

        $validated = $request->validated();
        $validated['notification_whatsapp'] = $request->boolean('notification_whatsapp');
        $validated['notification_sms'] = $request->boolean('notification_sms');
        $validated['notification_email'] = $request->boolean('notification_email');

        $setting->update($validated);

        return back()->with('success', __('Settings updated.'));
    }
}