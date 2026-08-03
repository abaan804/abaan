<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function privacy(): View
    {
        $section = PageContent::where('page_key', 'privacy_policy')
            ->where('section_key', 'body')
            ->first();

        return view('web.legal', [
            'pageTitle' => __('Privacy Policy'),
            'section' => $section,
        ]);
    }

    public function terms(): View
    {
        $section = PageContent::where('page_key', 'terms_conditions')
            ->where('section_key', 'body')
            ->first();

        return view('web.legal', [
            'pageTitle' => __('Terms & Conditions'),
            'section' => $section,
        ]);
    }
}