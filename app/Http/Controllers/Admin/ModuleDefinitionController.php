<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleDefinitionController extends Controller
{
    public function index(): View
    {
        $modules = ModuleDefinition::withCount('companyModules')
            ->orderBy('sort_order')
            ->get();

        return view('admin.modules.index', compact('modules'));
    }

    public function create(): View
    {
        return view('admin.modules.create', ['module' => new ModuleDefinition()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        ModuleDefinition::create($validated);

        return redirect()->route('admin.modules.index')->with('success', 'Module created successfully.');
    }

    public function edit(ModuleDefinition $module): View
    {
        return view('admin.modules.edit', compact('module'));
    }

    public function update(Request $request, ModuleDefinition $module): RedirectResponse
    {
        $validated = $this->validateRequest($request, $module->id);

        $module->update($validated);

        return redirect()->route('admin.modules.index')->with('success', 'Module updated successfully.');
    }

    public function destroy(ModuleDefinition $module): RedirectResponse
    {
        if ($module->companyModules()->exists()) {
            return back()->with('error', 'Cannot delete a module already assigned to companies. Set it to Disabled instead.');
        }

        $module->delete();

        return redirect()->route('admin.modules.index')->with('success', 'Module deleted successfully.');
    }

    protected function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'key' => 'required|alpha_dash|max:50|unique:module_definitions,key' . ($ignoreId ? ",{$ignoreId}" : ''),
            'name_en' => 'required|string|max:255',
            'name_ur' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_ur' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'status' => 'required|in:active,coming_soon,disabled',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}