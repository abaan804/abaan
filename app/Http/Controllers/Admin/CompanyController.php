<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $query = Company::query()->withCount('users')->with('subscriptions');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $companies = $query->latest()->paginate(15)->withQueryString();

        return view('admin.companies.index', compact('companies'));
    }

    public function show(Company $company): View
    {
        $company->load([
            'users' => fn ($q) => $q->orderByDesc('created_at'),
            'subscriptions' => fn ($q) => $q->with('package')->latest(),
            'transactions' => fn ($q) => $q->latest()->take(10),
            'companyModules.moduleDefinition',
        ]);

        return view('admin.companies.show', compact('company'));
    }

    public function suspend(Company $company): RedirectResponse
    {
        $company->update(['status' => 'suspended']);

        return back()->with('success', "{$company->name} has been suspended.");
    }

    public function activate(Company $company): RedirectResponse
    {
        $company->update(['status' => 'active']);

        return back()->with('success', "{$company->name} has been activated.");
    }
}