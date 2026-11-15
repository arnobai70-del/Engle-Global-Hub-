<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewModerationController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), [Review::STATUS_PENDING, Review::STATUS_APPROVED, Review::STATUS_REJECTED], true)
            ? (string) $request->query('status') : Review::STATUS_PENDING;

        return view('admin.reviews.index', [
            'reviews' => Review::query()->where('status', $status)->with('user:id,name,email')->latest('id')->paginate(25)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function update(Request $request, Review $review, ActivityRecorder $activity): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in([Review::STATUS_APPROVED, Review::STATUS_REJECTED])]]);

        $review->forceFill([
            'status' => $data['status'],
            'moderated_by_user_id' => $request->user()->getKey(),
            'moderated_at' => now(),
        ])->save();
        $activity->record($request->user(), 'review.'.$data['status'], $review);

        return back()->with('status', 'Review '.$data['status'].'.');
    }
}
