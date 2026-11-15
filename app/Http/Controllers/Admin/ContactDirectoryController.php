<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactDirectoryEntry;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactDirectoryController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(): View
    {
        return view('admin.contact-directory.index', [
            'entries' => ContactDirectoryEntry::query()->orderBy('kind')->orderBy('sort_order')->get(),
            'phoneCount' => ContactDirectoryEntry::query()->where('kind', ContactDirectoryEntry::KIND_PHONE)->count(),
            'ipCount' => ContactDirectoryEntry::query()->where('kind', ContactDirectoryEntry::KIND_IP_24X7)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        $limit = $data['kind'] === ContactDirectoryEntry::KIND_IP_24X7 ? ContactDirectoryEntry::MAX_IP_LINES : ContactDirectoryEntry::MAX_PHONES;
        if (ContactDirectoryEntry::query()->where('kind', $data['kind'])->count() >= $limit) {
            return back()->withErrors(['kind' => $data['kind'] === ContactDirectoryEntry::KIND_IP_24X7
                ? 'Only one 24/7 IP calling number is allowed.'
                : 'The directory holds up to ten phone numbers.'])->withInput();
        }

        $entry = ContactDirectoryEntry::query()->create($data);
        $this->activity->record($request->user(), 'contact_directory.created', $entry, ['kind' => $entry->kind]);

        return back()->with('status', 'Contact added.');
    }

    public function update(Request $request, ContactDirectoryEntry $entry): RedirectResponse
    {
        $data = $this->validated($request, $entry);
        unset($data['kind']);
        $entry->update($data);
        $this->activity->record($request->user(), 'contact_directory.updated', $entry, ['active' => $entry->is_active]);

        return back()->with('status', 'Contact updated.');
    }

    public function destroy(Request $request, ContactDirectoryEntry $entry): RedirectResponse
    {
        $this->activity->record($request->user(), 'contact_directory.deleted', $entry, ['kind' => $entry->kind]);
        $entry->delete();

        return back()->with('status', 'Contact removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?ContactDirectoryEntry $entry): array
    {
        $request->merge(['is_active' => $request->boolean('is_active'), 'is_public' => $request->boolean('is_public')]);

        return $request->validate([
            'kind' => [$entry ? 'nullable' : 'required', Rule::in([ContactDirectoryEntry::KIND_PHONE, ContactDirectoryEntry::KIND_IP_24X7])],
            'label' => ['required', 'string', 'max:80'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{3,32}$/', Rule::unique('contact_directory_entries', 'phone')->ignore($entry)],
            'availability' => ['nullable', 'string', 'max:120'],
            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ], ['phone.unique' => 'This number is already in the directory.']);
    }
}
