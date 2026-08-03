<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ModuleDefinition;
use App\Models\ModuleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function index(Request $request): View
    {
        $company = $request->user()->company;
        $company->load('companyModules');

        $modules = ModuleDefinition::orderBy('sort_order')->get();
        $assigned = $company->companyModules->keyBy('module_definition_id');

        $pendingRequests = ModuleRequest::where('company_id', $company->id)
            ->where('status', 'pending')
            ->pluck('module_definition_id')
            ->toArray();

        return view('tenant.modules.index', compact('modules', 'assigned', 'pendingRequests'));
    }

    public function request(Request $request, ModuleDefinition $module): RedirectResponse
    {
        abort_unless($request->user()->can('manage company subscription'), 403);

        $company = $request->user()->company;

        $alreadyEnabled = $company->companyModules()
            ->where('module_definition_id', $module->id)
            ->where('is_enabled', true)
            ->exists();

        if ($alreadyEnabled) {
            return back()->with('error', __('This module is already enabled for your company.'));
        }

        $alreadyPending = ModuleRequest::where('company_id', $company->id)
            ->where('module_definition_id', $module->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return back()->with('error', __('You already have a pending request for this module.'));
        }

        ModuleRequest::create([
            'company_id' => $company->id,
            'module_definition_id' => $module->id,
            'requested_by' => $request->user()->id,
            'status' => 'pending',
        ]);

        return back()->with('success', __('Your request for :module has been sent to our team.', ['module' => $module->translated('name')]));
    }
}