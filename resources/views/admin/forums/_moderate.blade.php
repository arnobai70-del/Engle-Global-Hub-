<div style="display:flex;flex-wrap:wrap;gap:6px">
@if($post->status !== 'visible')<form method="POST" action="{{ route('admin.forums.posts.moderate', $post) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="visible"><button class="egh-button">Show</button></form>@endif
@if($post->status !== 'hidden')<form method="POST" action="{{ route('admin.forums.posts.moderate', $post) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="hidden"><button class="egh-button danger">Hide</button></form>@endif
<form method="POST" action="{{ route('admin.forums.threads.lock', $post->thread) }}">@csrf @method('PATCH')<input type="hidden" name="is_locked" value="{{ $post->thread->is_locked ? 0 : 1 }}"><button class="egh-button secondary">{{ $post->thread->is_locked ? 'Unlock topic' : 'Lock topic' }}</button></form>
</div>
