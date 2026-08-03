<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ModuleDefinition;
use App\Models\PageContent;
use App\Models\Package;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $sections = PageContent::forPage('home');
        $modules = ModuleDefinition::orderBy('sort_order')->get();
        $packages = Package::where('status', 'active')->orderBy('sort_order')->take(3)->get();

        return view('web.home', compact('sections', 'modules', 'packages'));
    }
}