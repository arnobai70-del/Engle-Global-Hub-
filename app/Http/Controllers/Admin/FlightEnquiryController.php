<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlightEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class FlightEnquiryController extends Controller
{
    private const STATUSES = [
        'new_enquiry', 'reviewing', 'quotation_sent', 'accepted',
        'payment_confirmed', 'ticket_issued', 'cancelled', 'expired',
    ];

    public function index(): View
    {
        return view('admin.flight-enquiries.index', [
            'enquiries' => FlightEnquiry::query()->latest()->paginate(25),
        ]);
    }

    public function show(FlightEnquiry $enquiry): View
    {
        return view('admin.flight-enquiries.show', compact('enquiry'));
    }

    public function update(Request $request, FlightEnquiry $enquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'quoted_total_bdt' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);
        if (in_array($data['status'], ['quotation_sent', 'accepted', 'payment_confirmed', 'ticket_issued'], true)
            && (empty($data['quoted_total_bdt']) && empty($enquiry->quoted_total_bdt))) {
            return back()->withErrors(['quoted_total_bdt' => 'A BDT quotation is required for this status.']);
        }
        // This CRM status is an admin-entered manual record, not supplier confirmation.
        $enquiry->forceFill([
            'status' => $data['status'],
            'quoted_total_bdt' => $data['quoted_total_bdt'] ?? $enquiry->quoted_total_bdt,
            'admin_notes' => $data['admin_notes'] ?? $enquiry->admin_notes,
        ])->save();

        return back()->with('status', 'Flight enquiry updated.');
    }
}
