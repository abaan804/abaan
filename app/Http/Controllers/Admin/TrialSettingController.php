<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\TrialSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrialSettingController extends Controller
{
    public function index(): View
    {
        $globalSetting = TrialSetting::whereNull('applies_to_package_id')->first();

        $packages = Package::where('status', 'active')
            ->with(['trialSettingOverride' => fn ($q) => $q->whereNotNull('applies_to_package_id')])
            ->orderBy('sort_order')
            ->get();

        return view('admin.trial-settings.index', compact('globalSetting', 'packages'));
    }

    public function updateGlobal(Request $request): RedirectResponse
    {
        $request->validate([
            'is_enabled' => 'boolean',
            'duration_days' => 'required|integer|min:1|max:365',
        ]);

        TrialSetting::updateOrCreate(
            ['applies_to_package_id' => null],
            [
                'is_enabled' => $request->boolean('is_enabled'),
                'duration_days' => $request->duration_days,
            ]
        );

        return back()->with('success', 'Global trial settings updated.');
    }

    public function updatePackageOverride(Request $request, Package $package): RedirectResponse
    {
        $request->validate([
            'override_enabled' => 'boolean',
            'is_enabled' => 'boolean',
            'duration_days' => 'nullable|integer|min:1|max:365',
        ]);

        if (! $request->boolean('override_enabled')) {
            // Remove override — package falls back to global setting
            TrialSetting::where('applies_to_package_id', $package->id)->delete();

            return back()->with('success', "Override removed for {$package->name_en} — now using global trial settings.");
        }

        $request->validate([
            'duration_days' => 'required|integer|min:1|max:365',
        ]);

        TrialSetting::updateOrCreate(
            ['applies_to_package_id' => $package->id],
            [
                'is_enabled' => $request->boolean('is_enabled'),
                'duration_days' => $request->duration_days,
            ]
        );

        return back()->with('success', "Trial override updated for {$package->name_en}.");
    }
}