<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use App\Models\PageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('web.contact', ['sections' => PageContent::forPage('contact')]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        ContactMessage::create($request->validated());

        return back()->with('success', __('Thank you — we have received your message and will get back to you shortly.'));
    }
}