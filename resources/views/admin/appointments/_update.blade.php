<form method="POST" action="{{ route('admin.appointments.update', $appointment) }}" style="display:grid;gap:6px;min-width:220px">@csrf @method('PATCH')
<input type="datetime-local" name="scheduled_at" value="{{ $appointment->scheduled_at->format('Y-m-d\TH:i') }}" aria-label="Date and time" required>
<input type="hidden" name="duration_minutes" value="{{ $appointment->duration_minutes }}">
<select name="mode" aria-label="Mode">@foreach(\App\Models\Appointment::MODES as $value => $label)<option value="{{ $value }}" @selected($appointment->mode === $value)>{{ $label }}</option>@endforeach</select>
<select name="staff_user_id" aria-label="Staff"><option value="">Unassigned</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected((int) $appointment->staff_user_id === $member->id)>{{ $member->name }}</option>@endforeach</select>
<input name="location_or_link" value="{{ $appointment->location_or_link }}" maxlength="500" placeholder="Office address or meeting link (shown to student)" aria-label="Location or link">
<select name="status" aria-label="Status">@foreach(\App\Models\Appointment::STATUSES as $value => $label)<option value="{{ $value }}" @selected($appointment->status === $value)>{{ $label }}</option>@endforeach</select>
<button class="egh-button secondary">Save</button></form>
