<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('web.about', ['sections' => PageContent::forPage('about')]);
    }

    public function services(): View
    {
        return view('web.services', ['sections' => PageContent::forPage('services')]);
    }

    public function features(): View
    {
        return view('web.features', ['sections' => PageContent::forPage('features')]);
    }
}