<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use App\Models\Package;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $sections = PageContent::forPage('pricing');
        $packages = Package::where('status', 'active')
            ->with('features')
            ->orderBy('sort_order')
            ->get();

        return view('web.pricing', compact('sections', 'packages'));
    }
}