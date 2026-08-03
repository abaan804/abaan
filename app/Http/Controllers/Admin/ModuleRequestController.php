<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = ModuleRequest::with(['company', 'moduleDefinition', 'requestedBy']);

        if ($status = $request->get('status', 'pending')) {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(20)->withQueryString();
        $pendingCount = ModuleRequest::where('status', 'pending')->count();

        return view('admin.module-requests.index', compact('requests', 'pendingCount'));
    }

    public function approve(ModuleRequest $moduleRequest): RedirectResponse
    {
        $moduleRequest->company->companyModules()->updateOrCreate(
            ['module_definition_id' => $moduleRequest->module_definition_id],
            ['is_enabled' => true, 'enabled_at' => now()]
        );

        $moduleRequest->update(['status' => 'approved']);

        return back()->with('success', __('Module enabled for :company.', ['company' => $moduleRequest->company->name]));
    }

    public function decline(ModuleRequest $moduleRequest): RedirectResponse
    {
        $moduleRequest->update(['status' => 'declined']);

        return back()->with('success', __('Request declined.'));
    }
}