<?php

namespace App\Http\Controllers;

use App\Enums\BusinessService;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('reviews.index', [
            'reviews' => Review::query()->where('status', Review::STATUS_APPROVED)->latest('moderated_at')
                ->paginate(20, ['id', 'service', 'rating', 'title', 'body', 'display_name', 'moderated_at']),
            'services' => BusinessService::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service' => ['nullable', Rule::enum(BusinessService::class)],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:3000'],
            'display_name' => ['required', 'string', 'max:80'],
        ]);

        $request->user()->reviews()->create($data);

        return redirect()->route('reviews.index')->with('status', 'Thank you. Your review will appear after our team checks it.');
    }
}
