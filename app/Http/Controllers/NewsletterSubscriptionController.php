<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:180'],
        ]);

        NewsletterSubscriber::query()->updateOrCreate(
            ['email' => strtolower($validated['email'])],
            ['subscribed_at' => now()],
        );

        return back()->with('newsletter_status', 'Thanks — your email was added to the newsletter list.');
    }
}
