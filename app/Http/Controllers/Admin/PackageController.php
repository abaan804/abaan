<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleDefinition;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        $packages = Package::withCount('subscriptions')
            ->with('moduleDefinitions')
            ->orderBy('sort_order')
            ->get();

        return view('admin.packages.index', compact('packages'));
    }

    public function create(): View
    {
        $modules = ModuleDefinition::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return view('admin.packages.form', compact('modules'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255|unique:packages,name',
            'monthly_price'   => 'required|numeric|min:0',
            'trial_days'      => 'required|integer|min:0|max:365',
            'description'     => 'nullable|string',
            'is_active'       => 'nullable|boolean',
            'max_users'       => 'nullable|integer|min:1',
            'sort_order'      => 'nullable|integer|min:0',
            'module_ids'      => 'nullable|array',
            'module_ids.*'    => 'exists:module_definitions,id',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $package = Package::create($data);

        if (! empty($data['module_ids'])) {
            $package->moduleDefinitions()->sync($data['module_ids']);
        }

        return redirect()->route('admin.packages.index')
            ->with('success', __('Package created successfully.'));
    }

    public function edit(Package $package): View
    {
        $modules = ModuleDefinition::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $selectedModuleIds = $package->moduleDefinitions()->pluck('module_definitions.id')->toArray();

        return view('admin.packages.form', compact('package', 'modules', 'selectedModuleIds'));
    }

    public function update(Request $request, Package $package): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255|unique:packages,name_en,' . $package->id,
            'monthly_price' => 'required|numeric|min:0',
            'trial_days'    => 'required|integer|min:0|max:365',
            'description'   => 'nullable|string',
            'is_active'     => 'nullable|boolean',
            'max_users'     => 'nullable|integer|min:1',
            'sort_order'    => 'nullable|integer|min:0',
            'module_ids'    => 'nullable|array',
            'module_ids.*'  => 'exists:module_definitions,id',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $package->update($data);
        $package->moduleDefinitions()->sync($data['module_ids'] ?? []);

        return redirect()->route('admin.packages.index')
            ->with('success', __('Package updated.'));
    }

    public function destroy(Package $package): \Illuminate\Http\RedirectResponse
    {
        if ($package->subscriptions()->whereIn('status', ['trial', 'active'])->exists()) {
            return back()->with('error', __('Cannot delete a package with active subscriptions.'));
        }

        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', __('Package deleted.'));
    }

    public function json(Package $package): JsonResponse
    {
        $package->load('moduleDefinitions');
        return response()->json(['data' => $package]);
    }
}