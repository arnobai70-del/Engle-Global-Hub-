<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Services\Forum\ForumPostGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function __construct(private ForumPostGuard $guard) {}

    public function index(Request $request): View
    {
        return view('forums.index', [
            'categories' => ForumCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get()
                ->filter(fn (ForumCategory $category): bool => $category->isVisibleTo($request->user()))->values(),
        ]);
    }

    public function category(Request $request, string $category): View
    {
        $forum = $this->visibleCategory($request, $category);

        return view('forums.category', [
            'category' => $forum,
            'threads' => $forum->threads()->where('status', ForumPost::STATUS_VISIBLE)->with('user:id,name')
                ->orderByDesc('is_pinned')->orderByDesc('last_posted_at')->paginate(25),
        ]);
    }

    public function thread(Request $request, string $category, string $thread): View
    {
        $forum = $this->visibleCategory($request, $category);
        $topic = $forum->threads()->where('slug', $thread)->where('status', ForumPost::STATUS_VISIBLE)->firstOrFail();

        return view('forums.thread', [
            'category' => $forum,
            'thread' => $topic,
            'posts' => $topic->posts()->where('status', ForumPost::STATUS_VISIBLE)->with('user:id,name')->paginate(30),
        ]);
    }

    public function storeThread(Request $request, string $category): RedirectResponse
    {
        $forum = $this->visibleCategory($request, $category);
        $data = $request->validate([
            'title' => ['required', 'string', 'min:8', 'max:150'],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
            'website' => ['prohibited'],
        ]);
        $body = $this->guard->redact($data['body']);
        $this->guard->assertNotSpam($body, $request->user());
        $status = $forum->requires_approval ? ForumPost::STATUS_PENDING : ForumPost::STATUS_VISIBLE;

        $thread = DB::transaction(function () use ($forum, $data, $body, $status, $request): ForumThread {
            $thread = $forum->threads()->create([
                'user_id' => $request->user()->getKey(),
                'title' => $this->guard->redact($data['title']),
                'slug' => Str::slug(Str::limit($data['title'], 80, '')).'-'.Str::lower(Str::random(6)),
            ]);
            $thread->forceFill(['status' => $status, 'last_posted_at' => now()])->save();
            $post = $thread->posts()->create(['user_id' => $request->user()->getKey(), 'body' => $body, 'body_hash' => ForumPostGuard::hash($body)]);
            $post->forceFill(['status' => $status])->save();

            return $thread;
        });

        return $status === ForumPost::STATUS_VISIBLE
            ? redirect()->route('forums.thread', [$forum->slug, $thread->slug])->with('status', __('Your topic has been posted.'))
            : redirect()->route('forums.category', $forum->slug)->with('status', __('Thank you. Your topic will appear after a moderator approves it.'));
    }

    public function storeReply(Request $request, string $category, string $thread): RedirectResponse
    {
        $forum = $this->visibleCategory($request, $category);
        $topic = $forum->threads()->where('slug', $thread)->where('status', ForumPost::STATUS_VISIBLE)->firstOrFail();
        abort_if($topic->is_locked, 403, __('This topic is closed for replies.'));

        $data = $request->validate(['body' => ['required', 'string', 'min:5', 'max:5000'], 'website' => ['prohibited']]);
        $body = $this->guard->redact($data['body']);
        $this->guard->assertNotSpam($body, $request->user());
        $status = $forum->requires_approval ? ForumPost::STATUS_PENDING : ForumPost::STATUS_VISIBLE;

        $post = $topic->posts()->create(['user_id' => $request->user()->getKey(), 'body' => $body, 'body_hash' => ForumPostGuard::hash($body)]);
        $post->forceFill(['status' => $status])->save();
        if ($status === ForumPost::STATUS_VISIBLE) {
            $topic->forceFill(['last_posted_at' => now()])->save();
        }

        return back()->with('status', $status === ForumPost::STATUS_VISIBLE ? __('Reply posted.') : __('Your reply will appear after a moderator approves it.'));
    }

    public function report(Request $request, ForumPost $post): RedirectResponse
    {
        abort_unless($post->status === ForumPost::STATUS_VISIBLE && $post->thread->category->isVisibleTo($request->user()), 404);
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $post->reports()->firstOrCreate(['user_id' => $request->user()->getKey()], ['reason' => strip_tags($data['reason'])]);

        return back()->with('status', __('Thank you. A moderator will review this post.'));
    }

    private function visibleCategory(Request $request, string $slug): ForumCategory
    {
        $category = ForumCategory::query()->where('slug', $slug)->firstOrFail();
        abort_unless($category->isVisibleTo($request->user()), $request->user() ? 403 : 404);

        return $category;
    }
}
