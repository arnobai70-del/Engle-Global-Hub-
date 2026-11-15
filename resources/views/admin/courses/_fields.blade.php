<div class="stu-form-grid">
<label>Institution<select name="institution_id" required>@foreach($institutions as $institution)<option value="{{ $institution->id }}" @selected((string) old('institution_id', $course?->institution_id) === (string) $institution->id)>{{ $institution->name }}</option>@endforeach</select></label>
<label>Course name<input name="name" value="{{ old('name', $course?->name) }}" maxlength="200" required></label>
<label>Study level<select name="study_level"><option value="">Not specified</option>@foreach($levels as $value => $label)<option value="{{ $value }}" @selected(old('study_level', $course?->study_level) === $value)>{{ $label }}</option>@endforeach</select></label>
<label>Duration (months)<input type="number" name="duration_months" min="1" max="120" value="{{ old('duration_months', $course?->duration_months) }}"></label>
<label>Tuition note (as published by the institution)<input name="tuition_note" maxlength="300" value="{{ old('tuition_note', $course?->tuition_note) }}"></label>
<label>Source link (institution page, https)<input type="url" name="source_url" maxlength="2048" value="{{ old('source_url', $course?->source_url) }}"></label>
<label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $course?->is_active ?? true))> Active</label>
</div>
