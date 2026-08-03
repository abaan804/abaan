<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\View\View;

class FaqPageController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn ($faq) => $faq->category ?? __('General'));

        return view('web.faq', compact('faqs'));
    }
}