<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusinessService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStudyDestinationRequest;
use App\Models\MediaAsset;
use App\Models\ServiceDestination;
use App\Models\StudyDestination;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin › Study destinations: the /study-in-{slug} pages for every study country except
 * Malaysia and Singapore. The order is the existing Student Visa order from Admin ›
 * Services & Destinations (also used by the menu and the enquiry form).
 */
class StudyDestinationController extends Controller
{
    public function index(): View
    {
        $order = $this->studentVisaOrder();
        $destinations = StudyDestination::query()->with(['country', 'items'])->get()
            ->sortBy(fn (StudyDestination $destination): int => $order[$destination->country_id] ?? PHP_INT_MAX)->values();

        return view('admin.study-destinations.index', ['destinations' => $destinations, 'order' => $order]);
    }

    public function edit(StudyDestination $studyDestination): View
    {
        $studyDestination->load(['country', 'items', 'heroImage']);

        return view('admin.study-destinations.form', [
            'destination' => $studyDestination,
            'media' => MediaAsset::query()->orderBy('collection')->latest('id')->limit(200)->get(['id', 'path', 'alt_text', 'collection']),
        ]);
    }

    public function update(UpdateStudyDestinationRequest $request, StudyDestination $studyDestination, ActivityRecorder $activity): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($studyDestination, $data): void {
            $studyDestination->fill(collect($data)->except('items')->all())->save();
            $this->syncItems($studyDestination, $data['items'] ?? []);
        });
        $activity->record($request->user(), 'study-destination.updated', $studyDestination);

        $studyDestination->load('items');
        $missingSources = $studyDestination->factsWithoutSource()->count();
        $status = 'Saved.'.($missingSources > 0 ? ' '.$missingSources.' fact(s) have no source yet: add the official link before publishing them.' : '');
        if ($studyDestination->status === StudyDestination::STATUS_PUBLISHED && ! $studyDestination->isComplete()) {
            $status .= ' The page is published but hidden until these are done: '.implode(' ', $studyDestination->publishBlockers());
        }

        return redirect()->route('admin.study-destinations.edit', $studyDestination)->with('status', $status);
    }

    public function status(Request $request, StudyDestination $studyDestination, ActivityRecorder $activity): RedirectResponse
    {
        $status = $request->validate(['status' => ['required', Rule::in([StudyDestination::STATUS_DRAFT, StudyDestination::STATUS_PUBLISHED])]])['status'];
        $studyDestination->load('items');
        if ($status === StudyDestination::STATUS_PUBLISHED && ! $studyDestination->isComplete()) {
            return back()->withErrors(['status' => 'Not published yet: '.implode(' ', $studyDestination->publishBlockers())]);
        }

        $studyDestination->forceFill([
            'status' => $status,
            'published_at' => $status === StudyDestination::STATUS_PUBLISHED ? ($studyDestination->published_at ?? now()) : $studyDestination->published_at,
        ])->save();
        $activity->record($request->user(), 'study-destination.'.$status, $studyDestination);

        return back()->with('status', $status === StudyDestination::STATUS_PUBLISHED
            ? 'Published: /study-in-'.$studyDestination->slug.' is now public and the menu links to it.'
            : 'Unpublished: the menu links to the Student Visa enquiry form again.');
    }

    /**
     * Moves a destination up or down in the Student Visa order (Admin › Services & Destinations).
     */
    public function move(Request $request, StudyDestination $studyDestination): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];
        $rows = ServiceDestination::query()->where('service', BusinessService::StudentVisa->value)->orderBy('sort_order')->orderBy('id')->get()->values();
        $index = $rows->search(fn (ServiceDestination $row): bool => $row->country_id === $studyDestination->country_id);
        if ($index === false) {
            return back()->withErrors(['direction' => 'Add this country to Student Visa in Admin › Services & Destinations first.']);
        }
        $swapWith = $rows->get($direction === 'up' ? $index - 1 : $index + 1);
        if ($swapWith !== null) {
            DB::transaction(function () use ($rows, $index, $swapWith): void {
                // Renumber first so equal sort orders can still be swapped.
                $rows->each(fn (ServiceDestination $row, int $position) => $row->sort_order === $position * 10 ? null : $row->forceFill(['sort_order' => $position * 10])->save());
                $current = $rows->get($index);
                [$current->sort_order, $swapWith->sort_order] = [$swapWith->sort_order, $current->sort_order];
                $current->save();
                $swapWith->save();
            });
        }

        return back()->with('status', 'Order saved. The menu and the enquiry form use the same order.');
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $sections
     */
    private function syncItems(StudyDestination $destination, array $sections): void
    {
        foreach ($sections as $section => $rows) {
            if (! array_key_exists($section, StudyDestination::SECTIONS)) {
                continue;
            }
            $position = 0;
            foreach ($rows as $row) {
                $item = filled($row['id'] ?? null) ? $destination->items()->whereKey($row['id'])->where('section', $section)->first() : null;
                $values = [
                    'title' => $row['title'] ?? null, 'title_bn' => $row['title_bn'] ?? null,
                    'body' => $row['body'] ?? null, 'body_bn' => $row['body_bn'] ?? null,
                    'amount' => $row['amount'] ?? null,
                    'source_url' => $row['source_url'] ?? null, 'source_label' => $row['source_label'] ?? null,
                    'needs_verification' => (bool) ($row['needs_verification'] ?? false),
                ];
                $isEmpty = collect($values)->except('needs_verification')->filter(fn ($value) => filled($value))->isEmpty();
                if (($row['remove'] ?? false) || $isEmpty) {
                    $item?->delete();

                    continue;
                }
                $position += 10;
                $item !== null
                    ? $item->fill([...$values, 'sort_order' => $position])->save()
                    : $destination->items()->create([...$values, 'section' => $section, 'sort_order' => $position]);
            }
        }
    }

    /** @return array<int, int> country_id => position in the Student Visa order */
    private function studentVisaOrder(): array
    {
        return ServiceDestination::query()->where('service', BusinessService::StudentVisa->value)->orderBy('sort_order')->orderBy('id')
            ->pluck('country_id')->flip()->map(fn (int $position): int => $position)->all();
    }
}
