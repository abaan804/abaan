<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ModuleDefinition;
use App\Models\PageContent;
use Illuminate\View\View;

class SolutionsController extends Controller
{
    public function index(): View
    {
        $sections = PageContent::forPage('solutions');
        $modules = ModuleDefinition::orderBy('sort_order')->get();

        return view('web.solutions', compact('sections', 'modules'));
    }
}