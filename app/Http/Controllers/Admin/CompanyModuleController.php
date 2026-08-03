<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ModuleDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyModuleController extends Controller
{
    public function edit(Company $company): View
    {
        $modules = ModuleDefinition::orderBy('sort_order')->get();
        $company->load('companyModules');

        $assigned = $company->companyModules->keyBy('module_definition_id');

        return view('admin.companies.modules', compact('company', 'modules', 'assigned'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $enabledIds = $request->input('modules', []);

        foreach (ModuleDefinition::pluck('id') as $moduleId) {
            $company->companyModules()->updateOrCreate(
                ['module_definition_id' => $moduleId],
                [
                    'is_enabled' => in_array($moduleId, $enabledIds),
                    'enabled_at' => in_array($moduleId, $enabledIds) ? now() : null,
                ]
            );
        }

        return redirect()->route('admin.companies.show', $company)->with('success', 'Modules updated for ' . $company->name);
    }
}