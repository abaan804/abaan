<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateCompanyProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $company = $request->user()->company;

        return view('tenant.company.edit', compact('company'));
    }

    public function update(UpdateCompanyProfileRequest $request): RedirectResponse
    {
        $company = $request->user()->company;
        $validated = $request->safe()->except('logo');

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        $company->update($validated);

        return back()->with('success', __('Company profile updated successfully.'));
    }
}