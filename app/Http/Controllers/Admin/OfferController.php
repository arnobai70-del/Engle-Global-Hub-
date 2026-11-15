<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusinessService;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(): View
    {
        return view('admin.offers.index', ['offers' => Offer::query()->orderBy('sort_order')->orderByDesc('id')->paginate(25)]);
    }

    public function create(): View
    {
        return view('admin.offers.form', ['offer' => new Offer(['sort_order' => 0]), 'services' => BusinessService::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $offer = Offer::query()->create($this->validated($request, null));
        $this->activity->record($request->user(), 'offer.created', $offer, ['slug' => $offer->slug]);

        return redirect()->route('admin.offers.index')->with('status', 'Offer saved.');
    }

    public function edit(Offer $offer): View
    {
        return view('admin.offers.form', ['offer' => $offer, 'services' => BusinessService::cases()]);
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        $offer->update($this->validated($request, $offer));
        $this->activity->record($request->user(), 'offer.updated', $offer, ['slug' => $offer->slug, 'published' => $offer->is_published]);

        return redirect()->route('admin.offers.index')->with('status', 'Offer updated.');
    }

    public function destroy(Request $request, Offer $offer): RedirectResponse
    {
        $this->activity->record($request->user(), 'offer.deleted', $offer, ['slug' => $offer->slug]);
        $offer->delete();

        return redirect()->route('admin.offers.index')->with('status', 'Offer deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Offer $offer): array
    {
        $request->merge([
            'slug' => Str::slug((string) ($request->input('slug') ?: $request->input('title'))),
            'is_published' => $request->boolean('is_published'),
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:160', Rule::unique('offers', 'slug')->ignore($offer)],
            'service' => ['nullable', Rule::enum(BusinessService::class)],
            'summary' => ['required', 'string', 'max:300'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'valid_from' => ['nullable', 'date_format:Y-m-d'],
            'valid_until' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
            'is_published' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }
}
