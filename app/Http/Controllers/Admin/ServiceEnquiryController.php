<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BusinessService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentApplicationRequest;
use App\Models\ServiceEnquiry;
use App\Models\User;
use App\Notifications\ServiceEnquiryUpdated;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $service = BusinessService::tryFrom((string) $request->query('service', ''));
        $status = array_key_exists((string) $request->query('status'), ServiceEnquiry::STATUSES) ? (string) $request->query('status') : '';
        $search = trim((string) $request->query('search', ''));

        return view('admin.service-enquiries.index', [
            'enquiries' => ServiceEnquiry::query()->with(['destinationCountry:id,name', 'assignedTo:id,name'])
                ->when($service !== null, fn ($query) => $query->where('service', $service))
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('full_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')))
                ->latest('id')->paginate(20)->withQueryString(),
            'filters' => ['service' => $service?->value ?? '', 'status' => $status, 'search' => $search],
            'services' => array_map(fn (string $value): BusinessService => BusinessService::from($value), ServiceEnquiry::SERVICES),
        ]);
    }

    public function show(ServiceEnquiry $enquiry): View
    {
        return view('admin.service-enquiries.show', [
            'enquiry' => $enquiry->load(['destinationCountry:id,name', 'assignedTo:id,name', 'user:id,name,email', 'referredByAgent']),
            'staff' => User::query()->whereKey(StoreStudentApplicationRequest::staffUserIds())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, ServiceEnquiry $enquiry, ActivityRecorder $activity): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(ServiceEnquiry::STATUSES))],
            'assigned_to_user_id' => ['nullable', 'integer', Rule::in(StoreStudentApplicationRequest::staffUserIds())],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $previousStatus = $enquiry->status;
        $previousAssignee = $enquiry->assigned_to_user_id;
        $enquiry->forceFill($data)->save();

        if ($previousStatus !== $enquiry->status) {
            $enquiry->user?->notify(new ServiceEnquiryUpdated($enquiry, forStaff: false));
        }
        if ($enquiry->assigned_to_user_id !== null && (int) $previousAssignee !== (int) $enquiry->assigned_to_user_id && (int) $enquiry->assigned_to_user_id !== (int) $request->user()->getKey()) {
            $enquiry->assignedTo?->notify(new ServiceEnquiryUpdated($enquiry, forStaff: true));
        }
        $activity->record($request->user(), 'service_enquiry.updated', $enquiry, ['reference' => $enquiry->reference, 'status' => $data['status']]);

        return redirect()->route('admin.service-enquiries.show', $enquiry)->with('status', 'Enquiry updated.');
    }
}
