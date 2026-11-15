<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumReport;
use App\Models\ForumThread;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ForumModerationController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(): View
    {
        return view('admin.forums.index', [
            'categories' => ForumCategory::query()->withCount('threads')->orderBy('sort_order')->get(),
            'pending' => ForumPost::query()->where('status', ForumPost::STATUS_PENDING)->with(['user:id,name,email', 'thread.category'])->latest('id')->limit(50)->get(),
            'reports' => ForumReport::query()->whereNull('resolved_at')->with(['post.thread.category', 'post.user:id,name', 'user:id,name'])->latest('id')->limit(50)->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $request->merge(['slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name'))), 'requires_approval' => $request->boolean('requires_approval')]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('forum_categories', 'slug')],
            'description' => ['nullable', 'string', 'max:500'],
            'visibility' => ['required', Rule::in(ForumCategory::VISIBILITIES)],
            'requires_approval' => ['boolean'],
        ]);
        $category = ForumCategory::query()->create([...$data, 'is_active' => true, 'sort_order' => (int) ForumCategory::query()->max('sort_order') + 1]);
        $this->activity->record($request->user(), 'forum.category_created', $category, ['slug' => $category->slug]);

        return back()->with('status', 'Category created.');
    }

    public function updateCategory(Request $request, ForumCategory $category): RedirectResponse
    {
        $data = $request->validate(['visibility' => ['required', Rule::in(ForumCategory::VISIBILITIES)]]);
        $category->fill($data);
        $category->is_active = $request->boolean('is_active');
        $category->requires_approval = $request->boolean('requires_approval');
        $category->save();

        return back()->with('status', 'Category updated.');
    }

    public function moderatePost(Request $request, ForumPost $post): RedirectResponse
    {
        $status = $request->validate(['status' => ['required', Rule::in([ForumPost::STATUS_VISIBLE, ForumPost::STATUS_HIDDEN])]])['status'];
        $post->forceFill(['status' => $status])->save();

        $thread = $post->thread;
        if ($thread->posts()->orderBy('id')->value('id') === $post->getKey()) {
            $thread->forceFill(['status' => $status, 'last_posted_at' => $thread->last_posted_at ?? now()])->save();
        }
        ForumReport::query()->where('forum_post_id', $post->getKey())->whereNull('resolved_at')
            ->update(['resolved_at' => now(), 'resolved_by_user_id' => $request->user()->getKey()]);
        $this->activity->record($request->user(), 'forum.post_'.$status, $post);

        return back()->with('status', 'Post '.($status === ForumPost::STATUS_VISIBLE ? 'shown' : 'hidden').'.');
    }

    public function lockThread(Request $request, ForumThread $thread): RedirectResponse
    {
        $thread->forceFill(['is_locked' => $request->boolean('is_locked')])->save();
        $this->activity->record($request->user(), 'forum.thread_'.($thread->is_locked ? 'locked' : 'unlocked'), $thread);

        return back()->with('status', 'Topic updated.');
    }
}
