<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('status', 'active')->count(),
            'pending_companies' => Company::where('status', 'pending')->count(),
            'suspended_companies' => Company::where('status', 'suspended')->count(),

            'trial_subscriptions' => Subscription::where('status', 'trial')->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'cancelled_subscriptions' => Subscription::where('status', 'cancelled')->count(),
            'expired_subscriptions' => Subscription::where('status', 'expired')->count(),

            'total_users' => User::count(),

            'revenue_total' => Transaction::where('status', 'success')->sum('amount'),
        ];

        $recentCompanies = Company::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentCompanies'));
    }
}