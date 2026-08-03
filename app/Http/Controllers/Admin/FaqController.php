<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::orderBy('category')->orderBy('sort_order')->paginate(20);

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create(): View
    {
        return view('admin.faqs.create', ['faq' => new Faq()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validateRequest($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validateRequest($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted.');
    }

    protected function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'question_en' => 'required|string|max:255',
            'question_ur' => 'nullable|string|max:255',
            'question_ar' => 'nullable|string|max:255',
            'answer_en' => 'required|string',
            'answer_ur' => 'nullable|string',
            'answer_ar' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}