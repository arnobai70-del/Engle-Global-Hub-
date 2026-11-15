<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class MediaAssetController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(Request $request): View
    {
        $collection = array_key_exists((string) $request->query('collection'), MediaAsset::COLLECTIONS) ? (string) $request->query('collection') : '';

        return view('admin.media.index', [
            'assets' => MediaAsset::query()->when($collection !== '', fn ($query) => $query->where('collection', $collection))
                ->orderBy('collection')->orderBy('sort_order')->latest('id')->paginate(36)->withQueryString(),
            'collection' => $collection,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(4096)],
            'alt_text' => ['required', 'string', 'max:200'],
            'caption' => ['nullable', 'string', 'max:300'],
            'collection' => ['required', Rule::in(array_keys(MediaAsset::COLLECTIONS))],
        ]);

        $file = $request->file('image');
        $size = @getimagesize($file->getRealPath()) ?: [null, null];
        $asset = MediaAsset::query()->create([
            'collection' => $data['collection'],
            'path' => $file->store('media', 'public'),
            'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 200),
            'mime_type' => (string) $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'width' => $size[0],
            'height' => $size[1],
            'alt_text' => $data['alt_text'],
            'caption' => $data['caption'] ?? null,
            'is_published' => false,
            'sort_order' => (int) MediaAsset::query()->where('collection', $data['collection'])->max('sort_order') + 1,
            'uploaded_by_user_id' => $request->user()->getKey(),
        ]);
        $this->activity->record($request->user(), 'media.uploaded', $asset, ['collection' => $asset->collection]);

        return back()->with('status', 'Image uploaded as a draft.');
    }

    public function update(Request $request, MediaAsset $asset): RedirectResponse
    {
        $data = $request->validate([
            'alt_text' => ['required', 'string', 'max:200'],
            'caption' => ['nullable', 'string', 'max:300'],
            'collection' => ['required', Rule::in(array_keys(MediaAsset::COLLECTIONS))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);

        $asset->fill($data);
        $asset->is_published = $request->boolean('is_published');
        $asset->save();
        $this->activity->record($request->user(), 'media.updated', $asset, ['published' => $asset->is_published]);

        return back()->with('status', 'Image updated.');
    }

    public function destroy(Request $request, MediaAsset $asset): RedirectResponse
    {
        $this->activity->record($request->user(), 'media.deleted', $asset, ['collection' => $asset->collection]);
        $asset->delete();
        Storage::disk('public')->delete($asset->path);

        return back()->with('status', 'Image deleted.');
    }
}
