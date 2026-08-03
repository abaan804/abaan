<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageContentController extends Controller
{
    protected array $pages = [
        'home' => 'Home',
        'about' => 'About Us',
        'services' => 'Services',
        'solutions' => 'Solutions',
        'pricing' => 'Pricing',
        'features' => 'Features',
        'contact' => 'Contact Us',
        'privacy_policy' => 'Privacy Policy',
        'terms_conditions' => 'Terms & Conditions',
        'footer' => 'Footer',
    ];

    public function index(Request $request): View
    {
        $activePage = $request->get('page', 'home');

        $sections = PageContent::where('page_key', $activePage)
            ->orderBy('sort_order')
            ->get();

        return view('admin.content.index', [
            'pages' => $this->pages,
            'activePage' => $activePage,
            'sections' => $sections,
        ]);
    }

    public function update(Request $request, PageContent $content): RedirectResponse
    {
        $validated = $request->validate([
            'title_en' => 'nullable|string|max:255',
            'title_ur' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'content_en' => 'nullable|string',
            'content_ur' => 'nullable|string',
            'content_ar' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('page-content', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');

        $content->update($validated);

        return back()->with('success', "Section '{$content->section_key}' updated.");
    }
}